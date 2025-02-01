<?php

namespace App\Filament\FormGroups;

use App\Models\City;
use App\Models\State;
use App\Services\AddressServices;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class AddressFormGroup
{

    public static function make(Form $form): array
    {
        return [
            TextInput::make('zipcode')
                ->mask('99999-999')
                ->required()
                ->suffixActions([
                    Action::make('search')
                        ->icon('heroicon-o-magnifying-glass')
                        ->action(function(Set $set, $state, AddressServices $addressServices) {
                            if (blank($state)) {
                                Notification::make()
                                    ->title('Digite o CEP')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            try {
                                $zipcode = $addressServices->getZipCode($state);

                                $state = State::where('iso2', $zipcode['uf'])
                                    ->where('country_id', 31)
                                    ->first();

                                if (!$state) {
                                    throw new \Exception('Estado não encontrado');
                                }

                                $city = City::where('state_id', $state->id)
                                    ->where('name', $zipcode['localidade'])
                                    ->first();

                                if (!$city) {
                                    throw new \Exception('Cidade não encontrada');
                                }

                                $set('state_id', $state->id);
                                $set('city_id', $city->id);
                                $set('address', $zipcode['logradouro'] ?? null);
                                $set('district', $zipcode['bairro'] ?? null);

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                ]),
            Select::make('state_id')
                ->label('Estado')
                ->reactive()
                ->options(State::where('country_id', 31)->get()->pluck('name', 'id')),
            Select::make('city_id')
                ->key('city')
                ->label('Cidade')
                ->searchable()
                ->required()
                ->reactive()
                ->options(function (callable $get)
                {
                    $stateId = $get('state_id');

                    return $stateId
                        ? City::where('state_id', $stateId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                        : [];
                }),
            TextInput::make('address')
                ->columnSpan(3)
                ->label('Endereço')
                ->required(),
            TextInput::make('number')
                ->label('Número')
                ->required(),
            TextInput::make('district')
                ->label('Bairro')
                ->required(),
            TextInput::make('complement')
                ->label('Complemento'),
        ];
    }

}

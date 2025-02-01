<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Filament\FormGroups\AddressFormGroup;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Fieldset::make('Papel')
                        ->schema([
                            ToggleButtons::make('role_id')
                                ->hiddenLabel()
                                ->required()
                                ->options(Role::all()->sortBy('name')->pluck('name', 'id'))
                                ->default(2)
                                ->grouped(),
                        ]),
                    Fieldset::make('Papel')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label('Email')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('password')
                                ->label('Senha')
                                ->required()
                                ->maxLength(255)
                                ->confirmed()
                                ->rules([Password::min(8)->mixedCase()->numbers()->uncompromised()])
                                ->password()
                                ->revealable(),
                            TextInput::make('password_confirmation')
                                ->label('Confirme a Senha')
                                ->required()
                                ->maxLength(255)
                                ->password()
                                ->revealable(),
                        ]),
                ]),
                Fieldset::make('Dados de endereço')
                    ->schema([
                        Repeater::make('addresses')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema(AddressFormGroup::make($form))
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label("Nome")
                    ->searchable(),
                TextColumn::make('email')
                    ->label("E-mail")
                    ->searchable(),
                TextColumn::make('events_count')
                    ->label("Eventos que participou")
                    ->badge()
                    ->color(function (int $state) {
                        if ($state < 5) {
                            return "gray";
                        }

                        return "success";
                    })
                    ->counts(['events' => fn (Builder $query): Builder => $query->whereNotNull('checkin_at')]),
                TextColumn::make('created_at')
                    ->label("Data")
                    ->date("d F Y"),

            ])
            ->filters([
                SelectFilter::make('role_id')
                    ->label("Função")
                    ->relationship('role', 'name'),
                 QueryBuilder::make('events_count')
                    ->constraints([
                      RelationshipConstraint::make('events')
                        ->relationship('events', 'name')->multiple()
                    ])
            ])->filtersFormWidth(MaxWidth::Large)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

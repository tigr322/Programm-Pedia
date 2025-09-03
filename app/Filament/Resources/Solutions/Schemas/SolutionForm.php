<?php

namespace App\Filament\Resources\Solutions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SolutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('problem_id')
                    ->required()
                    ->numeric(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('summary')
                    ->columnSpanFull(),
                TextInput::make('environment'),
                Textarea::make('root_cause')
                    ->columnSpanFull(),
                Textarea::make('steps')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('verification')
                    ->columnSpanFull(),
                Textarea::make('pitfalls')
                    ->columnSpanFull(),
                TextInput::make('links'),
                TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

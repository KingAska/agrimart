<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                Section::make('Informasi Produk')->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(Product::class, 'slug', ignoreRecord: true),

                    RichEditor::make('description')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Galeri Produk')->schema([
                    Repeater::make('images')
                        ->relationship('images')
                        ->schema([
                            FileUpload::make('image_path')
                                ->image()
                                ->disk('public')
                                ->directory('product-images')
                                ->required(),
                        ])
                        ->grid(2)
                        ->itemLabel(fn (array $state): ?string => 'Gambar Produk')
                        ->addActionLabel('Tambah Gambar Lainnya')
                ]),
            ])->columnSpan(['lg' => 2]),

            Group::make()->schema([
                Section::make('Harga & Stok')->schema([
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('price')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),

                    TextInput::make('stock')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Status Aktif'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])
        ->columns(3);
    }
}

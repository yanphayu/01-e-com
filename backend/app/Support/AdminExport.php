<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class AdminExport
{
    public const MAX_ROWS = 2000;

    /**
     * @return array<string, string>
     */
    public static function columns(string $resource): array
    {
        return match ($resource) {
            'products' => [
                'id' => 'ID',
                'name' => 'Name',
                'category' => 'Category',
                'price' => 'Price',
                'status' => 'Status',
                'seller' => 'Seller',
                'is_active' => 'Active',
                'created_at' => 'Created at',
            ],
            'reports' => [
                'id' => 'ID',
                'product' => 'Product',
                'reporter' => 'Reporter',
                'reason' => 'Reason',
                'details' => 'Details',
                'status' => 'Status',
                'created_at' => 'Reported at',
            ],
            'users' => [
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'is_admin' => 'Admin',
                'products_count' => 'Listings',
                'created_at' => 'Joined at',
            ],
            'categories' => [
                'id' => 'ID',
                'name' => 'Name',
                'description' => 'Description',
                'subcategories_count' => 'Subcategories',
                'is_active' => 'Active',
                'created_at' => 'Created at',
            ],
            'subcategories' => [
                'id' => 'ID',
                'name' => 'Name',
                'category' => 'Category',
                'products_count' => 'Products',
                'is_active' => 'Active',
                'created_at' => 'Created at',
            ],
            'brands' => [
                'id' => 'ID',
                'name' => 'Name',
                'subcategory' => 'Subcategory',
                'models_count' => 'Models',
                'created_at' => 'Created at',
            ],
            'models' => [
                'id' => 'ID',
                'name' => 'Name',
                'brand' => 'Brand',
                'attributes_count' => 'Attributes',
                'created_at' => 'Created at',
            ],
            'attributes' => [
                'id' => 'ID',
                'name' => 'Name',
                'created_at' => 'Created at',
            ],
            default => throw new \InvalidArgumentException('Unsupported export resource.'),
        };
    }

    public static function title(string $resource): string
    {
        return ucfirst(str_replace('_', ' ', $resource));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function rows(string $resource, Collection $records): array
    {
        return $records->map(fn ($record): array => self::row($resource, $record))->all();
    }

    public static function pdf(string $resource, Collection $records, string $scope): Response
    {
        $columns = self::columns($resource);

        $pdf = Pdf::loadView('admin.exports.pdf', [
            'title' => self::title($resource),
            'scope' => $scope,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'columns' => array_values($columns),
            'rows' => self::rows($resource, $records),
        ])->setPaper('a4', 'landscape');

        $filename = self::filename($resource, 'pdf');

        return $pdf->stream($filename, ['Attachment' => true]);
    }

    public static function excel(string $resource, Collection $records): Response
    {
        $columns = self::columns($resource);
        $rows = self::rows($resource, $records);
        $filename = self::filename($resource, 'csv');

        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'wb');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_values($columns));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function limit(Builder $query): Builder
    {
        return $query->limit(self::MAX_ROWS);
    }

    private static function filename(string $resource, string $extension): string
    {
        return sprintf('%s-%s.%s', $resource, now()->format('Y-m-d-Hi'), $extension);
    }

    /**
     * @return array<int, string>
     */
    private static function row(string $resource, object $record): array
    {
        return match ($resource) {
            'products' => [
                (string) $record->id,
                (string) $record->name,
                (string) ($record->subcategory?->category?->name ?? '—'),
                (string) $record->price,
                (string) $record->status,
                (string) ($record->user?->name ?? '—'),
                $record->is_active ? 'Yes' : 'No',
                (string) $record->created_at,
            ],
            'reports' => [
                (string) $record->id,
                (string) ($record->product?->name ?? '—'),
                (string) ($record->user?->name ?? '—'),
                (string) $record->reason,
                (string) ($record->details ?? '—'),
                (string) $record->status,
                (string) $record->created_at,
            ],
            'users' => [
                (string) $record->id,
                (string) $record->name,
                (string) $record->email,
                (string) ($record->profile?->phone ?? '—'),
                $record->is_admin ? 'Yes' : 'No',
                (string) ($record->products_count ?? 0),
                (string) $record->created_at,
            ],
            'categories' => [
                (string) $record->id,
                (string) $record->name,
                (string) ($record->description ?? '—'),
                (string) ($record->subcategories_count ?? 0),
                $record->is_active ? 'Yes' : 'No',
                (string) $record->created_at,
            ],
            'subcategories' => [
                (string) $record->id,
                (string) $record->name,
                (string) ($record->category?->name ?? '—'),
                (string) ($record->products_count ?? 0),
                $record->is_active ? 'Yes' : 'No',
                (string) $record->created_at,
            ],
            'brands' => [
                (string) $record->id,
                (string) $record->name,
                (string) ($record->subcategory?->name ?? '—'),
                (string) ($record->models_count ?? 0),
                (string) $record->created_at,
            ],
            'models' => [
                (string) $record->id,
                (string) $record->name,
                (string) ($record->brand?->name ?? '—'),
                (string) ($record->attributes_count ?? 0),
                (string) $record->created_at,
            ],
            'attributes' => [
                (string) $record->id,
                (string) $record->name,
                (string) $record->created_at,
            ],
            default => throw new \InvalidArgumentException('Unsupported export resource.'),
        };
    }
}

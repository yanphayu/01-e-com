<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * @var array<string, class-string>
     */
    private const CONTROLLERS = [
        'products' => ProductController::class,
        'reports' => ReportController::class,
        'users' => UserController::class,
        'categories' => CategoryController::class,
        'subcategories' => SubcategoryController::class,
        'brands' => BrandController::class,
        'models' => ModelController::class,
        'attributes' => AttributeController::class,
    ];

    public function index(Request $request, string $resource): Response
    {
        abort_unless(isset(self::CONTROLLERS[$resource]), 404);

        $format = $request->query('format', 'pdf');
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        $scope = $request->query('scope') === 'all' ? 'all' : 'filtered';
        $records = AdminExport::limit($this->query($request, $resource))->get();

        return $format === 'excel'
            ? AdminExport::excel($resource, $records)
            : AdminExport::pdf($resource, $records, $scope);
    }

    private function query(Request $request, string $resource): Builder
    {
        $controller = self::CONTROLLERS[$resource];

        $instance = app($controller);

        abort_unless(method_exists($instance, 'filteredQuery'), 404);

        if ($request->query('scope') === 'all') {
            $request->query->remove('search');
            $request->query->remove('status');
            $request->query->remove('is_active');
            $request->query->remove('is_admin');
            $request->query->remove('category_id');
            $request->query->remove('subcategory_id');
            $request->query->remove('brand_id');
        }

        return $instance->filteredQuery($request);
    }
}

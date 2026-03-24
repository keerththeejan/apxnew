<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\PricingRule;
use App\Models\Setting;
use PDOException;

final class VehiclePricingController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-pricing');
    }

    public function index(): void
    {
        $this->requireAuth();

        $vehicleType = trim((string) Request::get('vehicle_type', ''));
        $page = (int) Request::get('page', 1);
        $result = PricingRule::paginate($vehicleType, $page, 20);
        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicle_pricing', [
            'title' => 'APX Admin - Vehicle Pricing',
            'pageKey' => 'vehicle_pricing',
            'pageTitle' => 'Vehicle Pricing',
            'crumb' => $siteName . ' / Vehicle Pricing',
            'vehicleType' => $vehicleType,
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => PricingRule::schemaReady(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        try {
            $id = PricingRule::create($this->payload());
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.create', 'pricing_rule', $id, null);
            $_SESSION['flash_success'] = 'Pricing rule saved.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to save pricing rule.';
        }
        $this->redirect('/admin/vehicle-pricing');
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid rule.';
            $this->redirect('/admin/vehicle-pricing');

            return;
        }
        PricingRule::update($id, $this->payload());
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.update', 'pricing_rule', $id, null);
        $_SESSION['flash_success'] = 'Pricing rule updated.';
        $this->redirect('/admin/vehicle-pricing');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid rule.';
            $this->redirect('/admin/vehicle-pricing');

            return;
        }
        PricingRule::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.delete', 'pricing_rule', $id, null);
        $_SESSION['flash_success'] = 'Pricing rule deleted.';
        $this->redirect('/admin/vehicle-pricing');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'branch_id' => (int) Request::post('branch_id', 1),
            'vehicle_type' => trim((string) Request::post('vehicle_type', 'car')),
            'base_fare' => Request::post('base_fare', 0),
            'per_km' => Request::post('per_km', 0),
            'per_hour' => Request::post('per_hour', 0),
            'per_day' => Request::post('per_day', 0),
            'waiting_per_hour' => Request::post('waiting_per_hour', 0),
            'extra_km_charge' => Request::post('extra_km_charge', 0),
            'extra_km_threshold' => Request::post('extra_km_threshold', 0),
            'night_charge_percent' => Request::post('night_charge_percent', 0),
            'peak_charge_percent' => Request::post('peak_charge_percent', 0),
            'peak_start' => trim((string) Request::post('peak_start', '')),
            'peak_end' => trim((string) Request::post('peak_end', '')),
            'night_start' => trim((string) Request::post('night_start', '')),
            'night_end' => trim((string) Request::post('night_end', '')),
            'is_active' => (int) Request::post('is_active', 1),
        ];
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }
}
<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\PricingRule;
use App\Models\Setting;
use PDOException;

final class VehiclePricingController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-pricing');
    }

    public function index(): void
    {
        $this->requireAuth();

        $vehicleType = trim((string) Request::get('vehicle_type', ''));
        $page = (int) Request::get('page', 1);
        $result = PricingRule::paginate($vehicleType, $page, 20);
        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicle_pricing', [
            'title' => 'APX Admin - Vehicle Pricing',
            'pageKey' => 'vehicle_pricing',
            'pageTitle' => 'Vehicle Pricing',
            'crumb' => $siteName . ' / Vehicle Pricing',
            'vehicleType' => $vehicleType,
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => PricingRule::schemaReady(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        try {
            $id = PricingRule::create($this->payload());
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.create', 'pricing_rule', $id, null);
            $_SESSION['flash_success'] = 'Pricing rule saved.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to save pricing rule.';
        }
        $this->redirect('/admin/vehicle-pricing');
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid rule.';
            $this->redirect('/admin/vehicle-pricing');

            return;
        }
        PricingRule::update($id, $this->payload());
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.update', 'pricing_rule', $id, null);
        $_SESSION['flash_success'] = 'Pricing rule updated.';
        $this->redirect('/admin/vehicle-pricing');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid rule.';
            $this->redirect('/admin/vehicle-pricing');

            return;
        }
        PricingRule::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_pricing.delete', 'pricing_rule', $id, null);
        $_SESSION['flash_success'] = 'Pricing rule deleted.';
        $this->redirect('/admin/vehicle-pricing');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'branch_id' => (int) Request::post('branch_id', 1),
            'vehicle_type' => trim((string) Request::post('vehicle_type', 'car')),
            'base_fare' => Request::post('base_fare', 0),
            'per_km' => Request::post('per_km', 0),
            'per_hour' => Request::post('per_hour', 0),
            'per_day' => Request::post('per_day', 0),
            'waiting_per_hour' => Request::post('waiting_per_hour', 0),
            'extra_km_charge' => Request::post('extra_km_charge', 0),
            'extra_km_threshold' => Request::post('extra_km_threshold', 0),
            'night_charge_percent' => Request::post('night_charge_percent', 0),
            'peak_charge_percent' => Request::post('peak_charge_percent', 0),
            'peak_start' => trim((string) Request::post('peak_start', '')),
            'peak_end' => trim((string) Request::post('peak_end', '')),
            'night_start' => trim((string) Request::post('night_start', '')),
            'night_end' => trim((string) Request::post('night_end', '')),
            'is_active' => (int) Request::post('is_active', 1),
        ];
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }
}

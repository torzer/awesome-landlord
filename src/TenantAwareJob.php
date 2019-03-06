<?php 

namespace Torzer\Awesome\Landlord;

use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Torzer\Awesome\Landlord\Facades\Landlord;

/**
 * Trait TenantAwareJob
 *
 * Use this trait instead of SerializesModels
 * in a job to ensure that a tenant is set
 * before the model is unserialised.
 */

trait TenantAwareJob
{
    use SerializesModels {
        __sleep as serializedSleep;
        __wakeup as serializedWakeup;
    }

    /**
     * The ID of the tenant to be used.
     *
     * @var int
     */
    protected $tenant_id;

    public function __sleep()
    {
        // If tenant was not overridden from "onTenant" method and exists a current tenant
        if (!$this->tenant_id && $tenant = Landlord::getTenantId('club_id')) {
            $this->tenant_id = $tenant;
        }

        $attributes = $this->serializedSleep();
        return $attributes;
    }

    public function __wakeup()
    {
        if (isset($this->tenant_id)) {
            Landlord::addTenant('club_id', $this->tenant_id);
        }
        $this->serializedWakeup();
    }

    /**
     * Manually override the tenant to be used on the queue.
     *
     * @param $tenant
     * @return $this
     */
    public function onTenant($tenant)
    {
        $this->tenant_id = ($tenant instanceof Model)
            ? $tenant->getKey()
            : $tenant;

        return $this;
    }
}

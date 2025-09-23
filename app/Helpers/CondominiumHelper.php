<?php

use App\Models\Condominium;

/**
 * Get the currently selected condominium instance.
 *
 * This function retrieves the condominium object that was
 * previously stored in the application container (via middleware).
 *
 * @return Condominium|null Returns the selected Condominium model, or null if not available.
 */
function selectedCondominium(): ?Condominium
{
    return app('selectedCondominium') ?? null;
}
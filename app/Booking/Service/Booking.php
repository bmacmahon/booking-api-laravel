<?php

namespace App\Booking\Service;

use App\Booking\Exception\PatientNotFound;
use App\Booking\Exception\Unavailable;
use App\Booking\Model\Availability as AvailabilityModel;
use App\Booking\Model\Clinic;
use App\Booking\Model\Patient;
use App\Booking\Model\Provider;
use Illuminate\Support\Facades\DB;

/**
 * Class Availability
 * @package App\Booking\Service
 */
class Booking
{
    /**
     * Book an availability
     *
     * @param int $patientId
     * @param int $providerId
     * @param string $startDatetime
     * @return int booking id
     * @throws Unavailable
     * @throws PatientNotFound
     */
    public function bookAvailability($patientName, $providerName, $startDatetime)
    {
        DB::beginTransaction();

        // validate patient
        $patient = Patient::select('id')
            ->where(DB::raw('CONCAT(first_name, last_name)'),  $patientName)
            ->first();

        if (!$patient || !$patient->exists()) {
            throw new PatientNotFound('No record found for ' . $patientName);
        }

        if (strtotime($startDatetime) < time()) {
            throw new Unavailable('Availability cannot be in the past: ' . $startDatetime);
        }

        // validate it exists and lock it
        $availability = AvailabilityModel::select('availability.id')
            ->join('provider', 'provider.id', '=', 'availability.provider_id')
            ->where(DB::raw('CONCAT(provider.first_name, provider.last_name)'),  $providerName)
            ->where('availability.start_datetime', $startDatetime)
            ->whereNull('availability.patient_id')
            ->lockForUpdate()
            ->first()
        ;

        if (!$availability || !$availability->exists()) {
            throw new Unavailable('No availability for providername ' . $providerName . ' at ' . $startDatetime);
        }

        // book it and give confirmation
        AvailabilityModel::where('id', $availability->id)
            ->update(['patient_id' =>  $patient->id]);

        DB::commit();

        return $availability->id;
    }

    /**
     * Get availabilities for a $providerId between $startDatetime and $endDatetime
     *
     * @param string $providerName
     * @param string $startDatetime
     * @param string $endDatetime
     * @return mixed
     */
    public function getAvailabilities($providerName, $startDatetime, $endDatetime)
    {
        $cols = [
            'availability.id AS availability_id',
            'availability.start_datetime',
            'availability.end_datetime'
        ];
        $availabilitiesQuery = \App\Booking\Model\Availability::select($cols)
            ->join('provider', 'provider.id', '=', 'availability.provider_id')
            ->where(DB::raw('CONCAT(provider.first_name, provider.last_name)'),  $providerName)
            ->where('availability.clinic_id',   Clinic::DEFAULT_ID)
            ->where('availability.start_datetime', '>=', $startDatetime)
            ->where('availability.start_datetime', '<=', $endDatetime)
            ->whereNull('patient_id')
        ;

        return $availabilitiesQuery->get();
    }

    /**
     * Get list of providers for $providerName
     *
     * @param string|null $providerName Name to search for, or return all if null
     * @return mixed
     */
    public function getProviders($providerName = null)
    {
        $cols = [
            DB::raw('UPPER(CONCAT(first_name, last_name)) AS PROVIDER_ID'),
            'first_name',
            'last_name'
        ];

        $providersQuery = Provider::select($cols)
            ->where('clinic_id', Clinic::DEFAULT_ID);

        if (null !== $providerName) {
            $providersQuery->where(DB::raw('CONCAT(first_name, last_name)'),  'like',  '%' . $providerName . '%');
        }

        return $providersQuery->get();
    }
}

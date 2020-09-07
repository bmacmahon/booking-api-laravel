<?php

namespace App\Http\Controllers;

use App\Booking\Exception\Unavailable;
use App\Booking\Service\Booking as AvailabilityService;
use Illuminate\Http\Request;

/**
 * Class BookingController
 * @package App\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * Display a listing of providers.
     *
     * @param string|null $providername Optional provider name to search by
     * @return string
     */
    public function getProviders($providername = null)
    {
        $service   = new AvailabilityService();
        $providers = $service->getProviders($providername);
        $json      = $providers->toJson(JSON_PRETTY_PRINT);

        return response($json, 200);
    }

    /**
     * Get availabilities for $providername between $start_datetime and $end_datetime
     *
     * @return string
     */
    public function getAvailabilities($providername, $start_datetime, $end_datetime)
    {
        $service        = new AvailabilityService();
        $availabilities = $service->getAvailabilities($providername, $start_datetime, $end_datetime);
        $json           = $availabilities->toJson(JSON_PRETTY_PRINT);

        return response($json, 200);
    }

    /**
     * Book an appointment for patientid with providerid at start_datetime
     *
     * @return Response
     */
    public function createAppointment(Request $request)
    {
        $providerName  = $request->post('providername');
        $startDatetime = $request->post('start_datetime');
        $patientName   = $request->post('patientname');

        if (empty($patientName) || empty($providerName) || empty($startDatetime)) {
            return response()->json([
                'fail' => 'providername, start_datetime, and patientname cannot be empty'
            ], 400);
        }

        try {
            $service   = new AvailabilityService();
            $bookingId = $service->bookAvailability($patientName, $providerName, $startDatetime);

            return response()->json([
                'success' => $bookingId
            ], 200);

        } catch (Unavailable $e) {
            return response()->json([
                'fail' => $e->getMessage()
                ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'fail' => $e->getMessage()
            ], 500);
        }
    }
}

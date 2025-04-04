<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller; // Import the base Controller


use App\Models\Certificate;
use Illuminate\Http\Request;

use Yabacon\Paystack; // Import Paystack




class CertificateFeeController extends Controller
{
    public function showPaymentPage($certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId);

        return view('certificates.fee', compact('certificate'));
    }



    public function processPayment(Request $request, $certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId); // Get the certificate

        // Fixed payment amount for the certificate in kobo
        $amount = 500 * 100; // 500 KES in kobo

        // Initialize Paystack payment
        $paystack = new \Yabacon\Paystack(env('PAYSTACK_SECRET_KEY'));

        $response = $paystack->transaction->initialize([
            'email' => $request->user()->email, // User's email
            'amount' => $amount, // Amount in kobo
            'callback_url' => route('certificates.paymentCallback'), // Callback URL
            'metadata' => [ // Pass additional data to callback
                'certificate_id' => $certificateId,
            ],
        ]);

        // Redirect user to Paystack payment page
        return redirect($response->data->authorization_url);
    }
    public function paymentCallback(Request $request)
    {
        $paystack = new \Yabacon\Paystack(env('PAYSTACK_SECRET_KEY'));

        // Verify the transaction using the reference
        $paymentDetails = $paystack->transaction->verify([
            'reference' => $request->query('reference'),
        ]);

        if ($paymentDetails->data->status == 'success') {
            // Retrieve the certificate ID from metadata
            $certificateId = $paymentDetails->data->metadata->certificate_id;

            // Update the database to mark the certificate fee as paid
            $certificate = Certificate::findOrFail($certificateId);
            $certificate->update(['certificate_fee_paid' => true]);
            // Redirect to a thank you page where the download will be triggered
            return redirect()->route('certificates.thankYouPage', ['certificateId' => $certificateId])
                ->with('success', 'Payment successful! Your certificate is ready for download.');
        }
        return redirect()->route('certificates.showPaymentPage', $certificateId)
            ->with('error', 'Payment failed. Please try again.');
    }

    public function thankYouPage($certificateId)
{
    $certificate = Certificate::findOrFail($certificateId);
    
    return view('certificates.thank-you', compact('certificate'));
}

}

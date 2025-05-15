import { Head } from '@inertiajs/react';

export default function PaymentSuccess() {
    return (
        <>
            <Head title="Payment Successful" />
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div className="text-center">
                        <svg className="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <h2 className="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Payment Successful!</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-300">Thank you for your payment. Your transaction has been completed successfully.</p>
                        <a href="/" className="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
} 
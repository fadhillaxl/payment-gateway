import { Head } from '@inertiajs/react';

interface Props {
    error?: string;
}

export default function PaymentError({ error }: Props) {
    return (
        <>
            <Head title="Payment Error" />
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div className="text-center">
                        <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <h2 className="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Payment Failed</h2>
                        {error && (
                            <p className="mt-2 text-red-600 dark:text-red-400">{error}</p>
                        )}
                        <p className="mt-2 text-gray-600 dark:text-gray-300">There was an error processing your payment. Please try again.</p>
                        <a href="/payment" className="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Try Again
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
} 
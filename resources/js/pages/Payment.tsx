import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

interface Props {
    snapToken: string;
    clientKey: string;
}

export default function Payment({ snapToken, clientKey }: Props) {
    useEffect(() => {
        // Load Midtrans script
        const script = document.createElement('script');
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', clientKey);
        script.async = true;
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, [clientKey]);

    const handlePayment = () => {
        // @ts-ignore
        window.snap.pay(snapToken, {
            onSuccess: function(result: any) {
                window.location.href = '/payment/success';
            },
            onPending: function(result: any) {
                window.location.href = '/payment/pending';
            },
            onError: function(result: any) {
                window.location.href = '/payment/error';
            }
        });
    };

    return (
        <>
            <Head title="Payment" />
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h1 className="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Payment</h1>
                    <button
                        onClick={handlePayment}
                        className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Bayar dengan Midtrans
                    </button>
                </div>
            </div>
        </>
    );
} 
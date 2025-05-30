<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $machine->name }} - Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-4">{{ $machine->name }}</h1>
            <p class="text-gray-600 mb-6">Location: {{ $machine->location }}</p>

            <form id="orderForm" class="space-y-4">
                <input type="hidden" name="token" value="{{ $machine->token }}">
                
                <div>
                    <label for="item" class="block text-sm font-medium text-gray-700">Select Item</label>
                    <select name="item" id="item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cola">Cola</option>
                        <option value="sprite">Sprite</option>
                        <option value="fanta">Fanta</option>
                        <option value="water">Water</option>
                    </select>
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" name="quantity" id="quantity" min="1" value="1" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <button type="submit" 
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Place Order
                </button>
            </form>

            <div id="message" class="mt-4 hidden"></div>
        </div>
    </div>

    <script>
        document.getElementById('orderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await axios.post('/vend/order', {
                    token: form.token.value,
                    item: form.item.value,
                    quantity: parseInt(form.quantity.value)
                });

                messageDiv.textContent = 'Order sent successfully!';
                messageDiv.className = 'mt-4 p-4 bg-green-100 text-green-700 rounded-md';
                messageDiv.classList.remove('hidden');
                
                // Reset form
                form.reset();
                form.quantity.value = 1;

            } catch (error) {
                messageDiv.textContent = error.response?.data?.message || 'Failed to send order';
                messageDiv.className = 'mt-4 p-4 bg-red-100 text-red-700 rounded-md';
                messageDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html> 
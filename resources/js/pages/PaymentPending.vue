<template>
  <div>
    <p :class="statusClass">{{ statusText }}</p>
    <button id="onButton" @click="turnOn">Turn On</button>
    <button id="offButton" @click="turnOff">Turn Off</button>

  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import { Client, Message } from 'paho-mqtt';


export default defineComponent({
  name: 'MQTTClient',
  setup() {
    const broker: string = '5e0dd55a391f437aa542e089127bdc2b.s1.eu.hivemq.cloud';
    const port: number = 8884;
    const clientId: string = 'webClient-' + Math.random().toString(16).substr(2, 8);
    const username: string = 'admin';
    const password: string = 'Admin123';
    const topic: string = 'led/control';

    const statusText = ref('Connecting...');
    const statusClass = ref('status connecting'); // This was likely the issue.

    let client: Client;

    const connect = () => {
      client = new Client(broker, port, clientId);

      client.onConnectionLost = onConnectionLost;
      client.onMessageArrived = onMessageArrived;

      client.connect({
        useSSL: true,
        userName: username,
        password: password,
        onSuccess: onConnect,
        onFailure: onFailure
      });
    };

    const onConnect = () => {
      console.log('Connected to MQTT broker');
      statusText.value = 'Connected';
      statusClass.value = 'status connected';
      const id_data = localStorage.getItem('selectedItemId');
      console.log('id_data:', id_data);

        if (id_data === '1') {
            console.log('id 1 selected');
            sendMessage('on');
        } else if (id_data === '2') {
            console.log('id 2 selected');
            // sendMessage('on');
        } else if (id_data === '3') {
            console.log('id 3 selected');
            // sendMessage('on');
        }
        
    };

    const onFailure = (message: { errorMessage: string }) => {
      console.log('Connection failed:', message.errorMessage);
      statusText.value = 'Connection failed';
      statusClass.value = 'status disconnected';
    };

    const onConnectionLost = (responseObject: { errorCode: number; errorMessage: string }) => {
      if (responseObject.errorCode !== 0) {
        console.log('Connection lost:', responseObject.errorMessage);
        statusText.value = 'Connection lost';
        statusClass.value = 'status disconnected';
      }
    };

    const onMessageArrived = (message: Message) => {
      console.log('Message arrived:', message.payloadString);
    };

    const sendMessage = (payload: string) => {
      const message = new Message(payload);
      message.destinationName = topic;
      client.send(message);
    };

    const turnOn = () => sendMessage('on');
    const turnOff = () => sendMessage('off');

    // Ensure everything is reactive and properly returned
    onMounted(() => {
      connect();
      turnOn();  // Optional: To automatically turn on LED when the page loads
    });

    return {
      statusText,  // Returning statusText and statusClass
      statusClass, // Returning statusClass
      turnOn,
      turnOff
    };
  }
});
</script>

<style scoped>
.status {
  font-weight: bold;
  margin-bottom: 1em;
}
.connected {
  color: green;
}
.disconnected {
  color: red;
}
.connecting {
  color: orange;
}
</style>

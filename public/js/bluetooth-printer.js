/**
 * Universal POS Thermal Printer Driver (Web Bluetooth & Web Serial USB)
 * Direct Connection with Persistent Memory & Smart User-Gesture Auto-Reconnect
 */
class PosThermalPrinter {
    constructor() {
        this.btDevice = null;
        this.btCharacteristic = null;
        this.serialPort = null;
        this.connectionType = null; // 'bluetooth' | 'serial'
        this.isConnected = false;
        this.deviceName = localStorage.getItem('pos_printer_name') || '';
        this.statusListeners = [];

        // 25+ Known Thermal Printer Bluetooth GATT Service UUIDs
        this.serviceUUIDs = [
            '000018f0-0000-1000-8000-00805f9b34fb', // 0x18F0 Standard Chinese Thermal
            '000018f1-0000-1000-8000-00805f9b34fb',
            '000018f2-0000-1000-8000-00805f9b34fb',
            '0000ff00-0000-1000-8000-00805f9b34fb', // 0xFF00 Rongta / Xprinter / POS-58
            '0000ff01-0000-1000-8000-00805f9b34fb',
            '0000ff02-0000-1000-8000-00805f9b34fb',
            '0000ffe0-0000-1000-8000-00805f9b34fb', // 0xFFE0 HM-10 / CC2541 BLE Module
            '0000ffe1-0000-1000-8000-00805f9b34fb',
            '0000fff0-0000-1000-8000-00805f9b34fb', // 0xFFF0 Goojprt / MPT-II
            '0000fff1-0000-1000-8000-00805f9b34fb',
            '0000fff2-0000-1000-8000-00805f9b34fb',
            '0000fee7-0000-1000-8000-00805f9b34fb', // 0xFEE7 WeChat/Tencent POS
            '0000ae00-0000-1000-8000-00805f9b34fb', // 0xAE00 POS printer
            '0000ae01-0000-1000-8000-00805f9b34fb',
            '0000ae30-0000-1000-8000-00805f9b34fb',
            '0000e0ff-0000-1000-8000-00805f9b34fb',
            '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC Transparent UART
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Nordic UART Service / Rongta
            '6e400001-b5a3-f393-e0a9-e50e24dcca9e',
            '00001101-0000-1000-8000-00805f9b34fb',
            '0000af30-0000-1000-8000-00805f9b34fb',
            '0000fef5-0000-1000-8000-00805f9b34fb',
            '0000180a-0000-1000-8000-00805f9b34fb'
        ];

        // 1. Coba auto-reconnect saat DOM dimuat
        if (typeof window !== 'undefined') {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    this.tryAutoReconnect();
                }, 300);
            });

            // 2. Browser Chrome membutuhkan 'User Gesture' (klik/tap) pertama kali setelah refresh untuk izin GATT
            const triggerUserGestureReconnect = async () => {
                if (!this.isConnected && localStorage.getItem('pos_printer_type')) {
                    await this.tryAutoReconnect();
                }
                window.removeEventListener('pointerdown', triggerUserGestureReconnect);
                window.removeEventListener('click', triggerUserGestureReconnect);
            };

            window.addEventListener('pointerdown', triggerUserGestureReconnect, { passive: true });
            window.addEventListener('click', triggerUserGestureReconnect, { passive: true });
        }
    }

    isBtSupported() {
        return typeof navigator !== 'undefined' && Boolean(navigator.bluetooth);
    }

    isSerialSupported() {
        return typeof navigator !== 'undefined' && Boolean(navigator.serial);
    }

    isSupported() {
        return this.isBtSupported() || this.isSerialSupported();
    }

    isSecure() {
        return typeof window !== 'undefined' && (window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');
    }

    getSavedPrinter() {
        return {
            name: localStorage.getItem('pos_printer_name') || '',
            type: localStorage.getItem('pos_printer_type') || ''
        };
    }

    onStatusChange(callback) {
        if (typeof callback === 'function') {
            this.statusListeners.push(callback);
            callback(this.isConnected, this.deviceName, this.connectionType);
        }
    }

    notifyStatus() {
        this.statusListeners.forEach(cb => cb(this.isConnected, this.deviceName, this.connectionType));
    }

    /**
     * Otomatis menyambungkan ulang printer yang sebelumnya pernah terhubung
     */
    async tryAutoReconnect() {
        if (this.isConnected) return true;

        const savedType = localStorage.getItem('pos_printer_type');
        if (!savedType) return false;

        try {
            // 1. AUTO RECONNECT SERIAL USB
            if (savedType === 'serial' && this.isSerialSupported()) {
                const ports = await navigator.serial.getPorts();
                if (ports && ports.length > 0) {
                    this.serialPort = ports[0];
                    await this.serialPort.open({ baudRate: 9600 });
                    this.deviceName = localStorage.getItem('pos_printer_name') || 'Printer USB Serial';
                    this.isConnected = true;
                    this.connectionType = 'serial';
                    this.notifyStatus();
                    console.log('⚡ Auto-reconnected to USB Serial Printer:', this.deviceName);
                    return true;
                }
            }

            // 2. AUTO RECONNECT BLUETOOTH
            if (savedType === 'bluetooth' && this.isBtSupported() && navigator.bluetooth.getDevices) {
                const devices = await navigator.bluetooth.getDevices();
                if (devices && devices.length > 0) {
                    this.btDevice = devices[0];
                    this.deviceName = this.btDevice.name || localStorage.getItem('pos_printer_name') || 'Printer Bluetooth';

                    this.btDevice.addEventListener('gattserverdisconnected', () => {
                        this.isConnected = false;
                        this.btCharacteristic = null;
                        this.notifyStatus();
                        console.log('Printer Bluetooth terputus.');
                    });

                    const server = await this.btDevice.gatt.connect();
                    this.btCharacteristic = await this.findBtWritableCharacteristic(server);

                    if (this.btCharacteristic) {
                        this.isConnected = true;
                        this.connectionType = 'bluetooth';
                        this.notifyStatus();
                        console.log('⚡ Auto-reconnected to Bluetooth Printer:', this.deviceName);
                        return true;
                    }
                }
            }
        } catch (err) {
            console.log('Auto reconnect info:', err.message);
        }
        return false;
    }

    /**
     * Hubungkan via Bluetooth
     */
    async connectBluetooth() {
        if (!this.isBtSupported()) {
            if (!this.isSecure()) {
                throw new Error('Web Bluetooth mewajibkan koneksi HTTPS atau localhost.');
            }
            throw new Error('Browser Anda belum mendukung Web Bluetooth. Gunakan Google Chrome.');
        }

        try {
            this.btDevice = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: this.serviceUUIDs
            });

            this.deviceName = this.btDevice.name || 'Printer Bluetooth';
            localStorage.setItem('pos_printer_name', this.deviceName);
            localStorage.setItem('pos_printer_type', 'bluetooth');

            this.btDevice.addEventListener('gattserverdisconnected', () => {
                this.isConnected = false;
                this.btCharacteristic = null;
                this.notifyStatus();
                console.log('Printer Bluetooth terputus.');
            });

            const server = await this.btDevice.gatt.connect();
            this.btCharacteristic = await this.findBtWritableCharacteristic(server);

            if (!this.btCharacteristic) {
                throw new Error('Tidak ditemukan port data (writable characteristic) pada printer ini.');
            }

            this.isConnected = true;
            this.connectionType = 'bluetooth';
            this.notifyStatus();
            return this.deviceName;
        } catch (error) {
            this.isConnected = false;
            this.btCharacteristic = null;
            this.connectionType = null;
            this.notifyStatus();
            throw error;
        }
    }

    async findBtWritableCharacteristic(server) {
        for (const serviceUuid of this.serviceUUIDs) {
            try {
                const service = await server.getPrimaryService(serviceUuid);
                const characteristics = await service.getCharacteristics();

                for (const char of characteristics) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        return char;
                    }
                }
            } catch (e) {
                continue;
            }
        }

        try {
            const services = await server.getPrimaryServices();
            for (const service of services) {
                try {
                    const characteristics = await service.getCharacteristics();
                    for (const char of characteristics) {
                        if (char.properties.write || char.properties.writeWithoutResponse) {
                            return char;
                        }
                    }
                } catch (e) { }
            }
        } catch (e) { }

        return null;
    }

    /**
     * Hubungkan via USB / Serial Cable
     */
    async connectSerial() {
        if (!this.isSerialSupported()) {
            throw new Error('Browser Anda belum mendukung Web Serial API. Gunakan Google Chrome / Edge di PC/Laptop.');
        }

        try {
            this.serialPort = await navigator.serial.requestPort();
            await this.serialPort.open({ baudRate: 9600 });

            this.deviceName = 'Printer USB Serial';
            localStorage.setItem('pos_printer_name', this.deviceName);
            localStorage.setItem('pos_printer_type', 'serial');

            this.isConnected = true;
            this.connectionType = 'serial';
            this.notifyStatus();
            return this.deviceName;
        } catch (error) {
            this.isConnected = false;
            this.serialPort = null;
            this.notifyStatus();
            throw error;
        }
    }

    /**
     * Putuskan koneksi printer
     */
    async disconnect() {
        localStorage.removeItem('pos_printer_type');
        localStorage.removeItem('pos_printer_name');

        if (this.btDevice && this.btDevice.gatt && this.btDevice.gatt.connected) {
            this.btDevice.gatt.disconnect();
        }
        if (this.serialPort) {
            try {
                await this.serialPort.close();
            } catch (e) { }
            this.serialPort = null;
        }
        this.isConnected = false;
        this.btCharacteristic = null;
        this.connectionType = null;
        this.deviceName = '';
        this.notifyStatus();
    }

    /**
     * Kirim data biner ESC/POS ke Printer
     */
    async printRawData(uint8Array) {
        if (!this.isConnected) {
            const reconnected = await this.tryAutoReconnect();
            if (!reconnected) {
                throw new Error('Printer belum terhubung. Silakan klik tombol "Printer" untuk menghubungkan.');
            }
        }

        // 1. KONEKSI SERIAL (USB/COM CABLE)
        if (this.connectionType === 'serial' && this.serialPort) {
            const writer = this.serialPort.writable.getWriter();
            try {
                await writer.write(uint8Array);
            } finally {
                writer.releaseLock();
            }
            return true;
        }

        // 2. KONEKSI BLUETOOTH
        if (this.connectionType === 'bluetooth') {
            if (!this.btCharacteristic) {
                if (this.btDevice && this.btDevice.gatt && !this.btDevice.gatt.connected) {
                    const server = await this.btDevice.gatt.connect();
                    this.btCharacteristic = await this.findBtWritableCharacteristic(server);
                } else {
                    throw new Error('Port data Bluetooth terputus.');
                }
            }

            const CHUNK_SIZE = 20; // 20 byte per paket (BLE ATT MTU)
            for (let i = 0; i < uint8Array.length; i += CHUNK_SIZE) {
                const chunk = uint8Array.slice(i, i + CHUNK_SIZE);
                if (this.btCharacteristic.properties.writeWithoutResponse) {
                    await this.btCharacteristic.writeValueWithoutResponse(chunk);
                } else {
                    await this.btCharacteristic.writeValue(chunk);
                }
                await new Promise(resolve => setTimeout(resolve, 20));
            }
            return true;
        }

        throw new Error('Tipe koneksi printer tidak valid.');
    }

    base64ToUint8Array(base64) {
        const binaryString = atob(base64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes;
    }

    async printInvoice(invoice) {
        const response = await fetch('/rawbt-struk/' + invoice);
        if (!response.ok) throw new Error('Gagal mengambil data struk.');
        const data = await response.json();

        if (!data.rawbt) throw new Error('Data struk kosong.');

        const uint8Array = this.base64ToUint8Array(data.rawbt);
        return await this.printRawData(uint8Array);
    }

    async printKitchen(invoice) {
        const response = await fetch('/rawbt-kitchen/' + invoice);
        if (!response.ok) throw new Error('Gagal mengambil data tiket dapur.');
        const data = await response.json();

        if (!data.rawbt) throw new Error('Data tiket dapur kosong.');

        const uint8Array = this.base64ToUint8Array(data.rawbt);
        return await this.printRawData(uint8Array);
    }

    async printShift(shiftId) {
        const response = await fetch('/rawbt-shift/' + shiftId);
        if (!response.ok) throw new Error('Gagal mengambil data rekap shift.');
        const data = await response.json();

        if (!data.rawbt) throw new Error('Data rekap shift kosong.');

        const uint8Array = this.base64ToUint8Array(data.rawbt);
        return await this.printRawData(uint8Array);
    }

    async testPrint() {
        const esc = "\x1b";
        let raw = esc + "@";
        raw += esc + "!\x00"; // Standard Font A (32 kolom)
        raw += esc + "a\x01"; // Center
        raw += esc + "!\x08TEST PRINTER POS\r\n" + esc + "!\x00";
        raw += "--------------------------------\r\n";
        raw += "Koneksi " + (this.connectionType === 'serial' ? 'USB Serial' : 'Bluetooth') + " Berhasil!\r\n";
        raw += "Printer: " + (this.deviceName || 'Thermal 58mm') + "\r\n";
        raw += "Waktu: " + new Date().toLocaleString('id-ID') + "\r\n";
        raw += "--------------------------------\r\n";
        raw += esc + "!\x08STATUS: SIAP DIGUNAKAN\r\n" + esc + "!\x00";
        raw += "\r\n\r\n\r\n\r\n";

        const encoder = new TextEncoder();
        const uint8Array = encoder.encode(raw);
        return await this.printRawData(uint8Array);
    }
}

// Global Singleton Instance
window.posBluetooth = new PosThermalPrinter();

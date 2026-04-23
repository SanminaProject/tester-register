<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                <div class="p-4 sm:p-6 text-gray-900">
                    <p class="text-center text-sm text-gray-500 mb-4">Point your camera at a QR code or barcode to scan</p>

                    @if (session('scan_error'))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('scan_error') }}
                        </div>
                    @endif
                    
                    <!-- Scanner Container -->
                    <div id="reader" class="mx-auto w-full max-w-xl aspect-square rounded-[24px] overflow-hidden shadow-inner border border-gray-100 bg-black"></div>
                    <div id="scan-status" class="mt-3 text-center text-xs text-gray-500">Initializing camera...</div>
                    
                    <!-- Result Container -->
                    <div id="result" class="mt-4 p-4 text-center bg-gray-50 rounded-xl hidden transition-all">
                        <span class="text-[13px] text-gray-500 block mb-1">Scanned Result</span>
                        <span id="result-text" class="text-lg font-bold text-gray-800 break-all"></span>
                        <span id="result-hint" class="mt-2 block text-xs text-gray-500"></span>
                    </div>

                    <!-- Try Again Button (Hidden initially) -->
                    <div id="retry-container" class="mt-4 text-center hidden">
                        <button onclick="window.location.reload()" class="px-6 py-2 bg-primary text-white text-sm font-semibold rounded-full shadow hover:bg-red-800 transition">
                            Scan Again
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Include html5-qrcode scanner library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        #reader {
            position: relative;
        }

        #reader video,
        #reader canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 24px;
        }

        #reader__scan_region {
            width: 100% !important;
            height: 100% !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <script>
        let html5QrCode = null;
        const scanTesterRouteTemplate = @json(route('scan.tester', ['id' => '__ID__']));

        function setStatus(message, isError = false) {
            const el = document.getElementById('scan-status');
            if (!el) return;
            el.textContent = message;
            el.className = isError
                ? 'mt-3 text-center text-xs text-red-600'
                : 'mt-3 text-center text-xs text-gray-500';
        }

        function extractTesterId(decodedText) {
            const raw = String(decodedText || '').trim();
            if (!raw) return null;

            // Accept plain numeric payload, e.g. "160"
            if (/^\d+$/.test(raw)) {
                return parseInt(raw, 10);
            }

            // Accept mixed payloads, e.g. "ID: 160" or "tester-160"
            const match = raw.match(/(\d{1,10})/);
            if (!match) return null;

            return parseInt(match[1], 10);
        }

        function showScanFeedback(decodedText, hint = '') {
            document.getElementById('result').classList.remove('hidden');
            document.getElementById('result-text').innerText = decodedText;
            document.getElementById('result-hint').innerText = hint;
            document.getElementById('retry-container').classList.remove('hidden');
        }

        function navigateToTesterDetails(testerId) {
            const target = scanTesterRouteTemplate.replace('__ID__', String(testerId));
            window.location.href = target;
        }

        function initScanner() {
            // Ensure reader exists on the page
            if (document.getElementById('reader') === null) return;

            if (html5QrCode === null) {
                html5QrCode = new Html5Qrcode("reader");
            }

            if (html5QrCode.isScanning) {
                return;
            }

            const qrCodeSuccessCallback = (decodedText) => {
                // Stop scanning when a result is found
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        const testerId = extractTesterId(decodedText);

                        if (!testerId) {
                            showScanFeedback(decodedText, 'Unable to read a valid Tester ID from this code.');
                            return;
                        }

                        showScanFeedback(decodedText, `Tester ID detected: ${testerId}. Redirecting...`);
                        navigateToTesterDetails(testerId);
                    }).catch((err) => {
                        console.log("Failed to stop scanner", err);
                    });
                }
            };

            const config = {
                fps: 12,
                aspectRatio: 1,
                // Keep a large square scan area for both QR and Code128.
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    return {
                        width: Math.floor(minEdge * 0.82),
                        height: Math.floor(minEdge * 0.82),
                    };
                },
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.CODE_128,
                ],
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true,
                },
            };

            setStatus('Requesting camera access...');

            html5QrCode.start({ facingMode: 'environment' }, config, qrCodeSuccessCallback)
                .then(() => {
                    setStatus('Camera is live. Align the code inside the scan area.');
                })
                .catch((err) => {
                    console.error('Camera error:', err);
                    setStatus('Camera start failed. Check browser camera permission and try again.', true);
                    document.getElementById('reader').innerHTML = `
                        <div class="flex items-center justify-center h-[250px] bg-gray-100 border-2 border-dashed border-gray-300 rounded-xl">
                            <div class="text-gray-500 text-center px-4">
                                <svg class="h-8 w-8 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p class="text-sm font-medium">Unable to start camera</p>
                                <p class="text-xs mt-1">Allow camera access in browser settings and reload this page.</p>
                            </div>
                        </div>`;
                });
        }

        // Run when DOM is ready (Full page load)
        document.addEventListener('DOMContentLoaded', () => {
            initScanner();
        });
        
        // Run when navigating via Livewire wire:navigate (SPA load)
        document.addEventListener('livewire:navigated', () => {
            initScanner();
        });

        // Stop the camera safely when navigating away via Livewire SPA
        document.addEventListener('livewire:navigating', () => {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    html5QrCode = null;
                }).catch(e => console.error("Error stopping scanner", e));
            }
        });
    </script>
</x-app-layout>
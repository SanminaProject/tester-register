<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                <div class="p-4 sm:p-6 text-gray-900">
                    <p class="text-center text-sm text-gray-500 mb-4">Point your camera at a QR code or barcode to scan</p>
                    
                    <!-- Scanner Container -->
                    <div id="reader" class="mx-auto w-full max-w-sm rounded-[24px] overflow-hidden shadow-inner border border-gray-100 bg-black"></div>
                    
                    <!-- Result Container -->
                    <div id="result" class="mt-4 p-4 text-center bg-gray-50 rounded-xl hidden transition-all">
                        <span class="text-[13px] text-gray-500 block mb-1">Scanned Result</span>
                        <span id="result-text" class="text-lg font-bold text-gray-800 break-all"></span>
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
    <script>
        let html5QrCode = null;

        function initScanner() {
            // Ensure reader exists on the page
            if (document.getElementById('reader') === null) return;

            if (html5QrCode === null) {
                html5QrCode = new Html5Qrcode("reader");
            }

            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                // Stop scanning when a result is found
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        // Display the resulting code
                        document.getElementById('result').classList.remove('hidden');
                        document.getElementById('result-text').innerText = decodedText;
                        document.getElementById('retry-container').classList.remove('hidden');
                    }).catch((err) => {
                        console.log("Failed to stop scanner", err);
                    });
                }
            };

            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 }, 
                aspectRatio: 1.0 
            };
            
            // Begin scanning with rear camera if not already scanning
            if (!html5QrCode.isScanning) {
                html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .catch(err => {
                    console.error("Camera error:", err);
                    document.getElementById('reader').innerHTML = `
                        <div class="flex items-center justify-center h-[250px] bg-gray-100 border-2 border-dashed border-gray-300 rounded-xl">
                            <div class="text-gray-500 text-center px-4">
                                <svg class="h-8 w-8 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p class="text-sm font-medium">Camera access denied</p>
                                <p class="text-xs mt-1">Please ensure your browser has camera permissions and you are using HTTPS.</p>
                            </div>
                        </div>`;
                });
            }
        }

        // Run when DOM is ready (Full page load)
        document.addEventListener('DOMContentLoaded', initScanner);
        
        // Run when navigating via Livewire wire:navigate (SPA load)
        document.addEventListener('livewire:navigated', initScanner);

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
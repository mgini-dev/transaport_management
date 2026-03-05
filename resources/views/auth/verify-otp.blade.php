<x-guest-layout>
    <div class="bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-lg mb-4 shadow-lg animate-float">
            <span class="text-4xl font-bold text-white">N</span>
        </div>
        <h1 class="text-3xl font-bold text-white">Nexus NMIS</h1>
        <p class="text-white/90 mt-2 text-sm">OTP Verification</p>
    </div>

    <div class="p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Enter OTP Code</h2>
            <p class="text-gray-600 mt-2 text-sm leading-relaxed">
                We sent a 6-digit OTP to <strong>{{ $email }}</strong>.
                Enter it below to continue.
            </p>
            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-left">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">OTP Expires In</p>
                <p id="otpExpiryCountdownValue" class="mt-1 text-2xl font-bold text-[#1b3b86]">
                    {{ gmdate('i:s', max(0, (int) ($otpExpiresInSeconds ?? 0))) }}
                </p>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div id="otpExpiryProgressBar"
                         class="h-full rounded-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] transition-[width] duration-1000 ease-linear"
                         style="width: 100%;"></div>
                </div>
                <p id="otpExpiryHint" class="mt-2 text-xs text-gray-500">
                    This OTP is valid for {{ $expiresInMinutes }} minutes.
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg text-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.verify') }}" class="space-y-6">
            @csrf
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                    OTP Code
                </label>
                <input id="otp"
                       type="text"
                       name="otp"
                       value="{{ old('otp') }}"
                       required
                       autofocus
                       inputmode="numeric"
                       maxlength="6"
                       autocomplete="one-time-code"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg tracking-[0.4em] text-center text-xl font-semibold focus:ring-2 focus:ring-[#1b3b86] focus:border-transparent transition"
                       placeholder="000000">
                @error('otp')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-[#1b3b86] to-[#2a9d8f] text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg hover:shadow-[#1b3b86]/20 transform hover:scale-[1.02] transition-all duration-200">
                Verify OTP
            </button>
        </form>

        <div class="mt-4 grid gap-3">
            <form method="POST" action="{{ route('password.otp.resend') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                    Resend OTP
                </button>
            </form>

            <a href="{{ route('password.request') }}"
               class="w-full flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                Change Email
            </a>
        </div>
    </div>

    <script>
        (function () {
            const expiryValue = document.getElementById('otpExpiryCountdownValue');
            const expiryProgressBar = document.getElementById('otpExpiryProgressBar');
            const expiryHint = document.getElementById('otpExpiryHint');

            if (!expiryValue || !expiryProgressBar || !expiryHint) {
                return;
            }

            let remainingSeconds = {{ (int) ($otpExpiresInSeconds ?? 0) }};
            const totalSeconds = Math.max({{ (int) ($otpTotalSeconds ?? 600) }}, 1);

            const formatSeconds = (seconds) => {
                const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                return `${minutes}:${secs}`;
            };

            const updateUi = () => {
                expiryValue.textContent = formatSeconds(Math.max(remainingSeconds, 0));
                const percent = Math.max(0, (remainingSeconds / totalSeconds) * 100);
                expiryProgressBar.style.width = `${percent}%`;
                if (remainingSeconds <= 0) {
                    expiryValue.classList.remove('text-[#1b3b86]');
                    expiryValue.classList.add('text-red-600');
                    expiryHint.classList.remove('text-gray-500');
                    expiryHint.classList.add('text-red-600');
                    expiryHint.textContent = 'OTP expired. Click Resend OTP to receive a new code.';
                } else {
                    expiryValue.classList.remove('text-red-600');
                    expiryValue.classList.add('text-[#1b3b86]');
                    expiryHint.classList.remove('text-red-600');
                    expiryHint.classList.add('text-gray-500');
                    expiryHint.textContent = 'This OTP is valid for {{ $expiresInMinutes }} minutes.';
                }
            };

            const tick = () => {
                updateUi();
                if (remainingSeconds <= 0) {
                    return;
                }
                remainingSeconds -= 1;
                setTimeout(tick, 1000);
            };

            tick();
        })();
    </script>
</x-guest-layout>

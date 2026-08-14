@extends('layouts.auth')

@section('title', 'Login')

@section('body')
    <section class="auth-wrapper">
        <div style="width:100%; max-width: 30rem;">
            <form class="style-box auth-box needs-validation" id="loginOtpForm" novalidate>
                @csrf
                <h1 class="hd-lg text-center my-2">Login</h1>

                {{-- Success Message --}}
                @if (session('success'))
                    <div style="color: green; margin-bottom: 10px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div id="otpMessage" style="display:none; margin-bottom: 10px;"></div>

                <div style="display: flex; gap: 1rem;">
                    <div class="form-floating flex-grow-1" id="emailBox">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="myemail" value="{{ old('email') }}" required>
                        <label for="myemail">Enter Registered Email</label>
                    </div>

                    <div class="form-floating flex-grow-1 d-none" id="otpBox">
                        <input type="text" name="otp" class="form-control" id="loginOtp" inputmode="numeric" maxlength="6" required>
                        <label for="loginOtp">Enter OTP</label>
                    </div>

                    <button type="submit" id="sendOtpBtn" class="btn-md btn-prim flex-shrink-0" title="Send OTP" style="min-width: 8.75rem;">
                       Send OTP <i class="fa-solid fa-arrow-right-long"></i>
                    </button>
                    <button type="button" id="verifyOtpBtn" class="btn-md btn-prim flex-shrink-0 d-none" style="min-width: 6rem;">Login</button>
                </div>

                <div id="otpActions" class="d-none" style="margin-top: .75rem; display: flex; justify-content: space-between; gap: 1rem; align-items: center;">
                    <button type="button" id="resendOtpBtn" class="p-0" style="color:var(--color-primary); font-size: .9rem;" disabled>Re-send OTP in 60s</button>
                    <button type="button" id="changeEmailBtn" class="p-0" style="color:var(--color-text-100); font-size: .9rem;">Change Email</button>
                </div>
            </form>
        </div>
    </section>
    <script>
        const form = document.getElementById('loginOtpForm');
        const emailBox = document.getElementById('emailBox');
        const otpBox = document.getElementById('otpBox');
        const emailInput = document.getElementById('myemail');
        const otpInput = document.getElementById('loginOtp');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        const changeEmailBtn = document.getElementById('changeEmailBtn');
        const otpActions = document.getElementById('otpActions');
        const otpMessage = document.getElementById('otpMessage');
        const csrfToken = document.querySelector('input[name="_token"]').value;
        let otpEmail = '';
        let resendTimer = null;

        function showMessage(message, isError = false, isInfo = false) {
            otpMessage.textContent = message;
            otpMessage.style.display = 'block';
            otpMessage.style.color = isError ? '#ff6b6b' : (isInfo ? '#8bb4ff' : '#2ecc71');
        }

        function setButtonLoading(button, isLoading, loadingText) {
            if (isLoading) {
                button.dataset.defaultHtml = button.dataset.defaultHtml || button.innerHTML;
                button.disabled = true;
                button.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${loadingText}`;
                return;
            }

            button.disabled = false;
            if (button.dataset.defaultHtml) {
                button.innerHTML = button.dataset.defaultHtml;
            }
        }

        function startResendTimer(seconds = 60) {
            clearInterval(resendTimer);
            let remaining = seconds;
            resendOtpBtn.disabled = true;
            resendOtpBtn.textContent = `Re-send OTP in ${remaining}s`;
            resendTimer = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(resendTimer);
                    resendOtpBtn.disabled = false;
                    resendOtpBtn.textContent = 'Re-send OTP';
                    return;
                }
                resendOtpBtn.textContent = `Re-send OTP in ${remaining}s`;
            }, 1000);
        }

        function showOtpStep(email) {
            otpEmail = email;
            emailBox.classList.add('d-none');
            sendOtpBtn.classList.add('d-none');
            otpBox.classList.remove('d-none');
            verifyOtpBtn.classList.remove('d-none');
            otpActions.classList.remove('d-none');
            otpInput.value = '';
            otpInput.focus();
            startResendTimer();
        }

        function showEmailStep() {
            clearInterval(resendTimer);
            otpEmail = '';
            otpBox.classList.add('d-none');
            verifyOtpBtn.classList.add('d-none');
            otpActions.classList.add('d-none');
            emailBox.classList.remove('d-none');
            sendOtpBtn.classList.remove('d-none');
            otpMessage.style.display = 'none';
            emailInput.focus();
        }

        async function postJson(url, data) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data),
            });

            let result = {};
            try {
                result = await response.json();
            } catch (error) {
                result = { message: 'Server error. Please try again.' };
            }

            if (!response.ok || result.success === false) {
                if (!result.message && result.errors) {
                    result.message = Object.values(result.errors).flat()[0];
                }
                throw result;
            }
            return result;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (otpEmail) {
                verifyOtpBtn.click();
                return;
            }

            setButtonLoading(sendOtpBtn, true, 'Sending...');
            showMessage('Sending OTP...', false, true);

            try {
                const result = await postJson('{{ route('login.otp.send') }}', { email: emailInput.value });
                showMessage(result.message || 'OTP sent successfully.');
                showOtpStep(emailInput.value);
            } catch (error) {
                showMessage(error.message || 'Unable to send OTP. Please try again.', true);
            } finally {
                setButtonLoading(sendOtpBtn, false);
            }
        });

        verifyOtpBtn.addEventListener('click', async function () {
            setButtonLoading(verifyOtpBtn, true, 'Checking...');

            try {
                const result = await postJson('{{ route('login.post') }}', { email: otpEmail, otp: otpInput.value });
                window.location.href = result.redirect || '{{ route('admin.dashboard.index') }}';
            } catch (error) {
                showMessage(error.message || 'Invalid OTP. Please try again.', true);
            } finally {
                setButtonLoading(verifyOtpBtn, false);
            }
        });

        resendOtpBtn.addEventListener('click', async function () {
            setButtonLoading(resendOtpBtn, true, 'Sending...');
            showMessage('Sending OTP again...', false, true);

            try {
                const result = await postJson('{{ route('login.otp.send') }}', { email: otpEmail });
                showMessage(result.message || 'OTP sent again.');
                startResendTimer(result.resend_after || 60);
            } catch (error) {
                showMessage(error.message || 'Unable to resend OTP. Please wait and try again.', true);
                if (error.resend_after) {
                    startResendTimer(error.resend_after);
                } else {
                    setButtonLoading(resendOtpBtn, false);
                }
            }
        });

        changeEmailBtn.addEventListener('click', function () {
            showEmailStep();
        });
    </script>
@endsection

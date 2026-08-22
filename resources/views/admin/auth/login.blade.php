@extends('layouts.auth')

@section('title', 'Login')

@section('body')
    <section class="auth-wrapper">
        <div style="width:100%; max-width: 45rem;">
            <form class="style-box auth-box" id="loginOtpForm" novalidate>
                @csrf
                <img src="{{ asset('images/account-logo.png') }}" alt="{{ config('app.name') }} logo" class="logo-img auth-logo-account"/>

                @if (session('success'))
                    <div class="auth-message auth-message-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div id="otpMessage" class="auth-message" role="alert" aria-live="polite" style="display:none;"></div>

                <div class="form-floating mb-1" id="emailBox">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           id="myemail" value="{{ old('email') }}" required>
                    <label for="myemail">Email</label>
                </div>

                <div class="form-floating mb-1 d-none" id="otpBox">
                    <input type="text" name="otp" class="form-control" id="loginOtp" inputmode="numeric"
                           pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
                    <label for="loginOtp">OTP</label>
                </div>

                <button type="submit" id="sendOtpBtn" class="btn-md btn-prim">
                    Send OTP <i class="fa-solid fa-arrow-right-long"></i>
                </button>

                <button type="button" id="verifyOtpBtn" class="btn-md btn-prim d-none">Login</button>

                <div id="otpActions" class="auth-otp-actions d-none">
                    <button type="button" id="resendOtpBtn" class="auth-link-btn" disabled>Re-send OTP in 60s</button>
                    <button type="button" id="changeEmailBtn" class="auth-link-btn">Change Email</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        const form = document.getElementById('loginOtpForm');
        const emailInput = document.getElementById('myemail');
        const otpBox = document.getElementById('otpBox');
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

        function showMessage(message, type = 'success') {
            otpMessage.textContent = message;
            otpMessage.className = `auth-message auth-message-${type}`;
            otpMessage.style.display = 'block';
        }

        function hideMessage() {
            otpMessage.textContent = '';
            otpMessage.style.display = 'none';
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
            let remaining = Math.max(Number(seconds) || 60, 1);
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

        function showOtpStep(email, resendAfter = 60) {
            otpEmail = email;
            emailInput.value = email;
            emailInput.readOnly = true;
            emailInput.classList.remove('is-invalid');
            otpBox.classList.remove('d-none');
            verifyOtpBtn.classList.remove('d-none');
            otpActions.classList.remove('d-none');
            sendOtpBtn.classList.add('d-none');
            otpInput.value = '';
            otpInput.classList.remove('is-invalid');
            otpInput.focus();
            startResendTimer(resendAfter);
        }

        function showEmailStep() {
            clearInterval(resendTimer);
            otpEmail = '';
            emailInput.readOnly = false;
            emailInput.classList.remove('is-invalid');
            otpInput.value = '';
            otpInput.classList.remove('is-invalid');
            otpBox.classList.add('d-none');
            verifyOtpBtn.classList.add('d-none');
            otpActions.classList.add('d-none');
            sendOtpBtn.classList.remove('d-none');
            resendOtpBtn.disabled = true;
            resendOtpBtn.textContent = 'Re-send OTP in 60s';
            hideMessage();
            emailInput.focus();
        }

        emailInput.addEventListener('input', function () {
            emailInput.classList.remove('is-invalid');
            hideMessage();
        });

        otpInput.addEventListener('input', function () {
            otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
            otpInput.classList.remove('is-invalid');
            hideMessage();
        });

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
                result.status = response.status;
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

            if (!emailInput.checkValidity()) {
                emailInput.classList.add('is-invalid');
                showMessage('Enter a valid registered email address.', 'error');
                emailInput.focus();
                return;
            }

            setButtonLoading(sendOtpBtn, true, 'Sending...');

            try {
                const result = await postJson('{{ route('login.otp.send') }}', { email: emailInput.value });
                showMessage(result.message || 'OTP sent successfully.', 'success');
                showOtpStep(emailInput.value, result.resend_after || 60);
            } catch (error) {
                if (error.resend_after) {
                    showMessage('OTP already sent. Please check your email.', 'info');
                    showOtpStep(emailInput.value, error.resend_after);
                    return;
                }

                emailInput.classList.add('is-invalid');
                showMessage(error.message || 'Unable to send OTP. Please try again.', 'error');
            } finally {
                setButtonLoading(sendOtpBtn, false);
            }
        });

        verifyOtpBtn.addEventListener('click', async function () {
            if (!/^\d{6}$/.test(otpInput.value)) {
                otpInput.classList.add('is-invalid');
                showMessage('Enter the 6-digit OTP sent to your email.', 'error');
                otpInput.focus();
                return;
            }

            setButtonLoading(verifyOtpBtn, true, 'Checking...');

            try {
                const result = await postJson('{{ route('login.post') }}', { email: otpEmail, otp: otpInput.value });
                window.location.href = result.redirect || '{{ route('admin.dashboard.index') }}';
            } catch (error) {
                otpInput.classList.add('is-invalid');
                showMessage(error.message || 'Invalid OTP. Please try again.', 'error');
            } finally {
                setButtonLoading(verifyOtpBtn, false);
            }
        });

        resendOtpBtn.addEventListener('click', async function () {
            setButtonLoading(resendOtpBtn, true, 'Sending...');

            try {
                const result = await postJson('{{ route('login.otp.send') }}', { email: otpEmail });
                showMessage(result.message || 'OTP sent again.', 'success');
                startResendTimer(result.resend_after || 60);
            } catch (error) {
                showMessage(error.message || 'Please wait before requesting another OTP.', error.resend_after ? 'info' : 'error');
                if (error.resend_after) {
                    startResendTimer(error.resend_after);
                }
            } finally {
                if (!resendOtpBtn.disabled) {
                    setButtonLoading(resendOtpBtn, false);
                } else if (resendOtpBtn.dataset.defaultHtml) {
                    resendOtpBtn.innerHTML = resendOtpBtn.textContent;
                }
            }
        });

        changeEmailBtn.addEventListener('click', function () {
            showEmailStep();
        });
    </script>
@endsection

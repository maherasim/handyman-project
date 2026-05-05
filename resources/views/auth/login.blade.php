<x-guest-layout>
   <style>
      .login-content .password-requirements { font-size: 0.875rem; }
      .login-content .password-requirements li { margin-bottom: 0.25rem; transition: color 0.15s ease; }
      .login-content .password-requirements li.valid { color: #198754; }
      .login-content .password-requirements li:not(.valid) { color: #6c757d; }
      .login-content .password-requirements li.valid .req-icon::before { content: '✓ '; font-weight: bold; }
      .login-content .password-requirements li:not(.valid) .req-icon::before { content: '○ '; }
      #login-submit:disabled { opacity: 0.65; cursor: not-allowed; }
   </style>
   <section class="login-content">
      <div class="container h-100">
         <div class="row align-items-center justify-content-center h-100">
            <div class="col-md-5">
               <div class="card p-3">
                  <div class="card-body">
                     <div class="auth-logo">
                        <a href="{{route('frontend.index')}}">
                           <img src="{{ getSingleMedia(imageSession('get'),'logo',null) }}" class="img-fluid rounded-normal" alt="logo">
                        </a>
                     </div>
                     <h3 class="mb-3 fw-bold text-center">{{__('auth.sign_in')}}</h3>
                     <p class="text-center text-secondary mb-4">{{__('auth.login_continue')}}</p>
                     <!-- Session Status -->
                     <x-auth-session-status class="mb-4" :status="session('status')" />

                     <!-- Validation Errors -->
                     <x-auth-validation-errors class="mb-4" :errors="$errors" />
                     <form method="POST" action="{{ route('login') }}" data-bs-toggle="validator">
                        {{csrf_field()}}
                        <div class="row">
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label class="text-secondary">{{__('auth.email')}} <span class="text-danger">*</span></label>
                                 <input id="email" name="email" value="{{request('email')}}" class="form-control" type="email" placeholder="{{ __('auth.enter_name',['name' => __('auth.email')]) }}" required autofocus>
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12 mt-2">
                              <div class="form-group">
                                 <div class="d-flex justify-content-between align-items-center">
                                    <label class="text-secondary">{{__('auth.login_password')}} <span class="text-danger">*</span></label>
                                 </div>
                                 <div class="input-group">
                                    <input id="password" class="form-control" type="password" value="{{request('password')}}" placeholder="{{ __('auth.enter_name',['name' => __('auth.login_password') ]) }}" name="password" required autocomplete="current-password">
                                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                       <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                    </span>
                                 </div>
                                 <p class="mb-1 mt-2 text-secondary small">{{ __('auth.password_requirements_intro') }}</p>
                                 <ul class="password-requirements list-unstyled mb-2" id="login-password-requirements" aria-live="polite">
                                    <li id="login-req-length"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_min') }}</li>
                                    <li id="login-req-letter"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_letter') }}</li>
                                    <li id="login-req-number"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_number') }}</li>
                                 </ul>
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12 mb-2">
                              <div class="d-flex justify-content-end align-items-center">
                                 <a href="{{route('password.request')}}" class="btn-link p-0 text-capitalize"><i>{{__('auth.forgot_password')}}</i></a>
                              </div>
                           </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-2 w-100" id="login-submit" disabled>{{ __('auth.login') }}</button>
                        <div class="text-center my-4 text-signup">
                           <label class="m-0 text-capitalize"> {{__('auth.dont_have_account')}}</label>
                           <a href="{{route('register')}}" class="ms-1 btn-link align-baseline text-capitalize">{{__('auth.signup')}}</a>
                       </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>

   <script>
      function loginPasswordPolicyCheck(pwd) {
         const p = pwd || '';
         return {
            lengthOk: p.length >= 8,
            letterOk: /[a-zA-Z]/.test(p),
            numberOk: /[0-9]/.test(p)
         };
      }

      function loginPasswordPolicySatisfied(pwd) {
         const c = loginPasswordPolicyCheck(pwd);
         return c.lengthOk && c.letterOk && c.numberOk;
      }

      function updateLoginPasswordRulesUi(pwd) {
         const c = loginPasswordPolicyCheck(pwd);
         document.getElementById('login-req-length')?.classList.toggle('valid', c.lengthOk);
         document.getElementById('login-req-letter')?.classList.toggle('valid', c.letterOk);
         document.getElementById('login-req-number')?.classList.toggle('valid', c.numberOk);
      }

      function updateLoginSubmitState() {
         const emailEl = document.getElementById('email');
         const passwordEl = document.getElementById('password');
         const btn = document.getElementById('login-submit');
         if (!emailEl || !passwordEl || !btn) return;
         const emailOk = emailEl.value.trim() !== '' && emailEl.checkValidity();
         const pwdOk = loginPasswordPolicySatisfied(passwordEl.value);
         updateLoginPasswordRulesUi(passwordEl.value);
         btn.disabled = !(emailOk && pwdOk);
      }

      document.addEventListener('DOMContentLoaded', function() {
         const emailEl = document.getElementById('email');
         const passwordEl = document.getElementById('password');
         if (emailEl) emailEl.addEventListener('input', updateLoginSubmitState);
         if (passwordEl) passwordEl.addEventListener('input', updateLoginSubmitState);
         updateLoginSubmitState();

         const togglePassword = document.getElementById('togglePassword');
         const passwordInput = document.getElementById('password');

         if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
               const icon = this.querySelector('i');
               const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
               passwordInput.setAttribute('type', type);

               if (type === 'password') {
                  icon.classList.remove('fa-eye');
                  icon.classList.add('fa-eye-slash');
               } else {
                  icon.classList.remove('fa-eye-slash');
                  icon.classList.add('fa-eye');
               }
               updateLoginSubmitState();
            });
         }
      });
   </script>
</x-guest-layout>
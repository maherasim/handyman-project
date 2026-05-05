<x-guest-layout>
   <style>
      /* Native selects only - no Select2; prevent overlapping */
      .login-content .form-group { margin-bottom: 1rem; }
      .login-content select.form-control { width: 100%; min-height: 38px; display: block; }
      .login-content #commission_section .form-group { margin-bottom: 1rem; }
      /* Required field indicators in red */
      .login-content label .text-danger,
      .login-content .form-group label span.text-danger {
         color: #dc3545 !important;
         font-weight: 600;
      }
      .password-requirements { font-size: 0.875rem; }
      .password-requirements li { margin-bottom: 0.25rem; transition: color 0.15s ease; }
      .password-requirements li.valid { color: #198754; }
      .password-requirements li:not(.valid) { color: #6c757d; }
      .password-requirements li.valid .req-icon::before { content: '✓ '; font-weight: bold; }
      .password-requirements li:not(.valid) .req-icon::before { content: '○ '; }
      #submit-btn:disabled { opacity: 0.65; cursor: not-allowed; }
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
                     <h3 class="mb-3 fw-bold text-center">{{__('auth.get_start')}}</h3>
                     <!-- Session Status -->
                     <x-auth-session-status class="mb-4" :status="session('status')" />

                     <!-- Validation Errors -->
                     <x-auth-validation-errors class="mb-4" :errors="$errors" />
                     <form method="POST" action="{{ route('register') }}" data-bs-toggle="validator">
                        {{csrf_field()}}
                        <div class="row">
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="username" class="text-secondary">{{__('auth.username')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" id="username" name="username" value="{{old('username')}}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.username') ]) }}">
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="first_name" class="text-secondary">{{__('auth.first_name')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" id="first_name" name="first_name" value="{{old('first_name')}}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.first_name') ]) }}">
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="last_name" class="text-secondary">{{__('auth.last_name')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" id="last_name" name="last_name" value="{{old('last_name')}}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.last_name') ]) }}">
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="email" class="text-secondary">{{__('auth.email')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" type="email" id="email" name="email" value="{{old('email')}}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.email') ]) }}" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,}">
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="password" class="text-secondary">{{__('auth.login_password')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" id="password" name="password" required autocomplete="new-password" placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.login_password') ]) }}">
                                 <p class="mb-1 mt-2 text-secondary small">{{ __('auth.password_requirements_intro') }}</p>
                                 <ul class="password-requirements list-unstyled mb-2" id="password-requirements-list" aria-live="polite">
                                    <li id="req-length"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_min') }}</li>
                                    <li id="req-letter"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_letter') }}</li>
                                    <li id="req-number"><span class="req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_number') }}</li>
                                 </ul>
                                 <small class="help-block with-errors text-danger"></small>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="password_confirmation" class="text-secondary">{{__('auth.confirm_password')}} <span class="text-danger">*</span></label>
                                 <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.confirm_password') ]) }}">
                                 <small class="help-block with-errors text-danger" id="confirm_passsword"></small>

                              </div>
                           </div>
                           <!-- User Type Selection (native select - no Select2 to avoid broken layout) -->
                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="user_type" class="text-secondary">{{ __('messages.user_type') }} <span class="text-danger">*</span></label>
                                 <select name="usertype" class="form-control mb-3" id="user_type">
                                    <option value="user">{{ __('landingpage.user') }}</option>
                                    <option value="provider">{{ __('messages.provider') }}</option>
                                    <option value="handyman">{{ __('messages.handyman') }}</option>
                                 </select>
                              </div>
                           </div>

                           <!-- Provider list (when Handyman is selected) -->
                           <div class="col-lg-12" id="provider_section" style="display: none;">
                              <div class="form-group">
                                 <label for="providerdata" class="text-secondary">{{ __('messages.provider') }}</label>
                                 <select name="provider_id" class="form-control mb-3" id="providerdata">
                                    <option value="">{{ __('messages.select_provider') }}</option>
                                 </select>
                              </div>
                           </div>

                           <!-- Commission Section (native selects only) -->
                           <div class="col-lg-12" id="commission_section">
                              {{-- Provider commission / provider type – commented out per request
                              <div class="form-group" id="providertype_group">
                                 <label for="providertype" class="text-secondary">{{ __('messages.user_commission') }} <span class="text-danger" id="commission_required">*</span></label>
                                 <select name="providertype_id" class="form-control mb-3" id="providertype">
                                    <option value="">{{ __('messages.select_provider_type') }}</option>
                                 </select>
                              </div>
                              --}}
                              <div class="form-group d-none" id="handymantype_group">
                                 <label for="handymantype" class="text-secondary">{{ __('messages.user_commission') }} <span class="text-danger">*</span></label>
                                 <select name="handymantype_id" class="form-control mb-3" id="handymantype">
                                    <option value="">{{ __('messages.select_handyman_type') }}</option>
                                 </select>
                              </div>
                           </div>

                           <div class="col-lg-12">
                              <div class="form-group">
                                 <label for="designation" class="text-secondary">{{__('messages.designation')}}</label>
                                 <input type="text" id="designation" name="designation" class="form-control" placeholder="{{__('placeholder.designation')}}" aria-label="designation"
                                    aria-describedby="basic-addon6">
                              </div>
                           </div>
                           <div class="col-lg-12 mt-2">
                              <div class="form-check mb-3 d-flex align-items-center">
                                 <input type="checkbox" class="form-check-input mt-0" id="customCheck1" required>
                                 <label class="form-check-label ps-2" for="customCheck1">
                                    {{-- {{__('auth.agree')}} <a class="btn-link p-0 text-capitalize" href="{{ url('/') }}/term-conditions">{{__('auth.term_service')}}</a> &amp; <a class="btn-link p-0 text-capitalize" href="{{ url('/') }}/privacy-policy">{{__('auth.privacy_policy')}}</a> --}}
                                    {{ __('auth.agree') }}
                                       <a class="btn-link p-0 text-capitalize" href="{{ url('term-conditions') }}">
                                          {{ __('auth.term_service') }}
                                       </a> &amp;
                                       <a class="btn-link p-0 text-capitalize" href="{{ url('privacy-policy') }}">
                                          {{ __('auth.privacy_policy') }}
                                       </a>

                                    <small class="help-block with-errors text-danger"></small>
                                 </label>
                              </div>
                           </div>

                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-2 w-100" id="submit-btn" disabled>{{ __('auth.create_account') }}</button>
                        <div class="col-lg-12 mt-3">
                           <p class="mb-0 text-center">{{__('auth.already_have_account')}} <a class="btn-link p-0 text-capitalize" href="{{route('login')}}">{{__('auth.sign_in')}}</a></p>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script>
         function passwordPolicyCheck(pwd) {
            const p = pwd || '';
            return {
               lengthOk: p.length >= 8,
               letterOk: /[a-zA-Z]/.test(p),
               numberOk: /[0-9]/.test(p)
            };
         }

         function passwordPolicySatisfied(pwd) {
            const c = passwordPolicyCheck(pwd);
            return c.lengthOk && c.letterOk && c.numberOk;
         }

         function updatePasswordRulesUi(pwd) {
            const c = passwordPolicyCheck(pwd);
            $('#req-length').toggleClass('valid', c.lengthOk);
            $('#req-letter').toggleClass('valid', c.letterOk);
            $('#req-number').toggleClass('valid', c.numberOk);
         }

         function registerFieldsReady() {
            const username = ($('#username').val() || '').trim();
            const first = ($('#first_name').val() || '').trim();
            const last = ($('#last_name').val() || '').trim();
            const email = ($('#email').val() || '').trim();
            const emailField = document.getElementById('email');
            const emailOk = email !== '' && emailField && emailField.checkValidity();
            if (!(username && first && last && emailOk)) return false;
            if (!$('#customCheck1').is(':checked')) return false;
            const ut = $('#user_type').val();
            if (ut === 'handyman') {
               if (!$('#providerdata').val()) return false;
               if (!$('#handymantype').val()) return false;
            }
            return true;
         }

         function updateRegisterSubmitState() {
            const password = $('#password').val() || '';
            const confirmPassword = $('#password_confirmation').val() || '';
            const errorElement = $('#confirm_passsword');
            const submitBtn = $('#submit-btn');

            updatePasswordRulesUi(password);

            const policyOk = passwordPolicySatisfied(password);
            const matchOk = confirmPassword.length > 0 && password === confirmPassword;
            if (confirmPassword.length > 0 && password !== confirmPassword) {
               errorElement.text(@json(__('auth.password_mismatch_error')));
            } else {
               errorElement.text('');
            }

            const ok = registerFieldsReady() && policyOk && matchOk;
            submitBtn.prop('disabled', !ok);
         }

         $(document).ready(function() {
            $('#password, #password_confirmation, #username, #first_name, #last_name, #email').on('input change', updateRegisterSubmitState);
            $('#customCheck1').on('change', updateRegisterSubmitState);
            $('#providerdata, #handymantype').on('change', updateRegisterSubmitState);
            updateRegisterSubmitState();

    function fetchTypes(userType, providerId = null) {
        $.ajax({
            url: '{{ route("ajax-list") }}',
            type: 'GET',
            data: {
                type: userType === 'provider' ? 'providertype' : 'handymantype',
                provider_id: providerId
            },
            success: function(response) {
                if (response.status === 'true') {
                    const targetDropdown = userType === 'provider' ? $('#providertype') : $('#handymantype');
                    targetDropdown.empty().append($('<option>', { value: '', text: userType === 'provider' ? '{{ __('messages.select_provider_type') }}' : '{{ __('messages.select_handyman_type') }}' }));
                    $.each(response.results, function(index, item) {
                        targetDropdown.append($('<option>', { value: item.id, text: item.text }));
                    });
                }
                updateRegisterSubmitState();
            },
            error: function() {
                console.error('Error fetching types');
            }
        });
    }

    function fetchProviders() {
        var baseURL = "{{ url('/') }}";
        $.ajax({
            url: baseURL + '/api/user-list',
            type: 'GET',
            data: { user_type: 'provider', status: 1, per_page: 25, page: 1 },
            success: function(response) {
                var $sel = $('#providerdata').empty().append($('<option>', { value: '', text: '{{ __('messages.select_provider') }}' }));
                if (response && response.data && response.data.length) {
                    $.each(response.data, function(index, item) {
                        $sel.append($('<option>', { value: item.id, text: (item.first_name || '') + ' ' + (item.last_name || '') }));
                    });
                } else {
                    $sel.append($('<option>', { value: '', text: '{{ __('messages.no_providers_found') }}' }));
                }
                updateRegisterSubmitState();
            },
            error: function() {
                console.error('Error fetching providers');
            }
        });
    }

    $('#user_type').change(function() {
        const selectedUserType = $(this).val();
        
        if (selectedUserType === 'user') {
            $('#provider_section').hide();
            $('#commission_section').hide();
            $('#providertype').prop('required', false);
            $('#handymantype').prop('required', false);
            setTimeout(updateRegisterSubmitState, 0);
        } else {
            $('#provider_section').toggle(selectedUserType === 'handyman');
            $('#commission_section').show();
            // providertype_group commented out – do not show or require for provider
            $('#providertype_group').hide();
            $('#handymantype_group').toggle(selectedUserType === 'handyman');
            if (selectedUserType === 'provider') {
                $('#providertype').prop('required', false);
                $('#handymantype').prop('required', false);
            } else if (selectedUserType === 'handyman') {
                $('#providertype').prop('required', false);
                $('#handymantype').prop('required', true);
                fetchProviders();
            }
            if (selectedUserType !== 'provider') fetchTypes(selectedUserType);
            $('#providertype').val('');
            $('#handymantype').val('');
            $('#providerdata').val('');
        }
        setTimeout(updateRegisterSubmitState, 0);
    }).trigger('change');

    $('#providerdata').change(function() {
        if ($('#user_type').val() === 'handyman') {
            var providerId = $(this).val();
            fetchTypes('handyman', providerId);
        }
        updateRegisterSubmitState();
    });
});

      </script>


   </section>
</x-guest-layout>

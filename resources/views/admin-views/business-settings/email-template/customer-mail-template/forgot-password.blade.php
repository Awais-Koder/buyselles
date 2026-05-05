<div>
    <div class="text-center">
        <img width="160" class="mb-4" id="view-mail-icon" src="{{ $template->image_full_url['path'] ?? dynamicAsset(path: 'public/assets/back-end/img/email-template/change-pass.png')}}" alt="">
        <h3 class="mb-3 view-mail-title text-capitalize">
            {{$title}}
        </h3>
    </div>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    <div>
        <p>{{translate('click_here')}} <br>
            @if(isset($data['passwordResetURL']))
                <a href="{{$data['passwordResetURL']}}">{{translate('change_password')}}</a>
            @endif
        </p>
    </div>
    @if(isset($data['verificationCode']))
        <div class="text-center mt-4">
            <h3 class="mb-2">{{translate('your_verification_code_is')}}:</h3>
            <h2 class="mb-3" style="font-weight: 700; color: #333; letter-spacing: 2px;">
                {{$data['verificationCode']}}
            </h2>
        </div>
    @endif
    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>

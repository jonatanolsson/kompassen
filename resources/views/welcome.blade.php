<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{ __('') }} <meta name="viewport" content="width=device-width, initial-scale=1">

        <title> {{ __('') }} <link rel="preconnect" href="https://fonts.bunny.net"> {{ __('') }} </head> {{ __('') }} <!-- Left Panel - Content --> {{ __('') }} <div class="flex justify-center opacity-50"> {{ __('') }} <svg class="h-4 text-zinc-800 dark:text-white" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg"> {{ __('') }} </line>
                                        <line x1="5" y1="1" x2="5" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"> {{ __('') }} </line>
                                        <line x1="13" y1="1" x2="13" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"> {{ __('') }} </line> {{ __('') }} </div>
                            <span class="text-xl font-semibold text-zinc-800 dark:text-white"> {{ __('') }} </div>

                    <flux:heading class="text-center" size="xl">{{ __('Welcome') }}</flux:heading> {{ __('') }} <flux:label for="email">{{ __('Email') }}</flux:label> {{ __('') }} <flux:field>
                            <flux:label for="password">{{ __('Password') }}</flux:label>
                            <flux:input id="password" type="password" placeholder="{{ __('Your password') }}" />
                        </flux:field>

                        <flux:checkbox label="{{ __('Remember me for 30 days') }}" />

                        @if (Route::has('login'))
                            <flux:button variant="primary" class="w-full" href="{{ route('login') }}" wire:navigate>
                                {{ __('Log in') }}
                            </flux:button>
                        @endif
                    </div>

                    @if (Route::has('register'))
                        <flux:subheading class="text-center">
                            {{ __('First time around here?') }}
                            <flux:link href="{{ route('register') }}" variant="subtle" class="text-sm" wire:navigate>
                                {{ __('Sign up for free') }}
                            </flux:link>
                        </flux:subheading> {{ __('') }} <!-- Right Panel - Illustration --> {{ __('') }} <!-- Animated background --> {{ __('') }} </div>
                        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"> {{ __('') }} <div class="relative z-10"> {{ __('') }} <flux:icon icon="star" variant="solid" class="text-yellow-400" /> {{ __('') }} <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        </div>

                        <div class="mb-6 italic font-base text-2xl xl:text-3xl text-white leading-relaxed">
                            {{ __('Comprehensive WCAG accessibility audits and professional reporting for your web applications.') }}
                        </div> {{ __('') }} <div class="flex flex-col justify-center font-medium">
                                <div class="text-lg text-white">{{ config('app.name') }}</div>
                                <div class="text-zinc-300">{{ __('Web Accessibility Auditing') }}</div> {{ __('') }} </div> {{ __('') }} </div>
    </body>
</html>

<div>
    <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="#"
                logo="https://fluxui.dev/img/demo/logo.png"
                logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
                name="Acme Inc."
            />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.search placeholder="Search..." />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" href="#" current>Home</flux:sidebar.item>
            <flux:sidebar.item icon="inbox" badge="12" href="#">Inbox</flux:sidebar.item>
            <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
            <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item>

            <flux:sidebar.group expandable heading="Favorites" class="grid">
                <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
                <flux:sidebar.item href="#">Android app</flux:sidebar.item>
                <flux:sidebar.item href="#">Brand guidelines</flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
            <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="/img/demo/user.png" name="Olivia Martin" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                    <flux:menu.radio>Truly Delta</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:profile avatar="/img/demo/user.png" />
    </flux:header>

    <flux:main container class="max-w-xl lg:max-w-3xl">
        <flux:heading size="xl">Settings</flux:heading>

        <flux:separator variant="subtle" class="my-8" />

        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
            <div class="w-80">
                <flux:heading size="lg">Profile</flux:heading>
                <flux:subheading>This is how others will see you on the site.</flux:subheading>
            </div>

            <div class="flex-1 space-y-6">
                <flux:input
                    label="Username"
                    description="This is your public display name. It can be your real name or a pseudonym. You can only change this once every 30 days."
                    placeholder="calebporzio"
                />

                <flux:select
                    label="Primary email"
                    description:trailing="You can manage verified email addresses in your email settings."
                    placeholder="Select primary email..."
                >
                    <flux:select.option>lotrrules22@aol.com</flux:select.option>
                    <flux:select.option>phantomatrix@hotmail.com</flux:select.option>
                </flux:select>

                <flux:textarea
                    label="Bio"
                    description:trailing="You can @mention other users and organizations to link to them."
                    placeholder="Tell us a little bit about yourself"
                />

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">Save profile</flux:button>
                </div>
            </div>
        </div>

        <flux:separator variant="subtle" class="my-8" />

        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
            <div class="w-80">
                <flux:heading size="lg">Preferences</flux:heading>
                <flux:subheading>Customize your layout and notification preferences.</flux:subheading>
            </div>

            <div class="flex-1 space-y-6">
                <flux:checkbox.group label="Sidebar" description="Select the items you want to display in the sidebar.">
                    <flux:checkbox value="recents" label="Recents" checked />
                    <flux:checkbox value="home" label="Home" checked />
                    <flux:checkbox value="applications" label="Applications" />
                    <flux:checkbox value="desktop" label="Desktop" />
                </flux:checkbox.group>

                <flux:separator variant="subtle" class="my-8" />

                <flux:radio.group label="Notify me about...">
                    <flux:radio value="all" label="All new messages" checked />
                    <flux:radio value="direct" label="Direct messages and mentions" />
                    <flux:radio value="none" label="Nothing" />
                </flux:radio.group>

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">Save preferences</flux:button>
                </div>
            </div>
        </div>

        <flux:separator variant="subtle" class="my-8" />

        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 pb-10">
            <div class="w-80">
                <flux:heading size="lg">Email notifications</flux:heading>
                <flux:subheading>Choose which emails you'd like to get from us.</flux:subheading>
            </div>

            <div class="flex-1 space-y-6">
                <flux:fieldset class="space-y-4">
                    <flux:switch checked label="Communication emails" description="Receive emails about your account activity." />

                    <flux:separator variant="subtle" />

                    <flux:switch checked label="Marketing emails" description="Receive emails about new products, features, and more." />

                    <flux:separator variant="subtle" />

                    <flux:switch label="Social emails" description="Receive emails for friend requests, follows, and more." />

                    <flux:separator variant="subtle" />

                    <flux:switch label="Security emails" description="Receive emails about your account activity and security." />
                </flux:fieldset>
            </div>
        </div>
    </flux:main>
</div>

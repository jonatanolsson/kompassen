<div>
    <flux:header sticky container class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-600">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

        <flux:navbar class="max-lg:hidden -mb-px">
            <flux:navbar.item href="#" data-current>{{ __('Dashboard') }}</flux:navbar.item>
            <flux:navbar.item href="#" badge="32">{{ __('Orders') }}</flux:navbar.item>
            <flux:navbar.item href="#">{{ __('Catalog') }}</flux:navbar.item>
            <flux:navbar.item href="#">{{ __('Configuration') }}</flux:navbar.item>
        </flux:navbar>
    </flux:header>

    <flux:sidebar collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:sidebar.nav>
            <flux:sidebar.item href="#" data-current>{{ __('Dashboard') }}</flux:sidebar.item>
            <flux:sidebar.item href="#" badge="32">{{ __('Orders') }}</flux:sidebar.item>
            <flux:sidebar.item href="#">{{ __('Catalog') }}</flux:sidebar.item>
            <flux:sidebar.item href="#">{{ __('Configuration') }}</flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

    <flux:main container>
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <flux:select size="sm" class="">
                        <flux:select.option>{{ __('Last 7 days') }}</flux:select.option>
                        <flux:select.option>{{ __('Last 14 days') }}</flux:select.option>
                        <flux:select.option selected>{{ __('Last 30 days') }}</flux:select.option>
                        <flux:select.option>{{ __('Last 60 days') }}</flux:select.option>
                        <flux:select.option>{{ __('Last 90 days') }}</flux:select.option>
                    </flux:select>

                    <flux:subheading class="max-md:hidden whitespace-nowrap">{{ __('compared to') }}</flux:subheading>

                    <flux:select size="sm" class="max-md:hidden">
                        <flux:select.option selected>{{ __('Previous period') }}</flux:select.option>
                        <flux:select.option>{{ __('Same period last year') }}</flux:select.option>
                        <flux:select.option>{{ __('Last month') }}</flux:select.option>
                        <flux:select.option>{{ __('Last quarter') }}</flux:select.option>
                        <flux:select.option>{{ __('Last 6 months') }}</flux:select.option>
                        <flux:select.option>{{ __('Last 12 months') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:separator vertical class="max-lg:hidden mx-2 my-2" />

                <div class="max-lg:hidden flex justify-start items-center gap-2">
                    <flux:subheading class="whitespace-nowrap">{{ __('Filter by:') }}</flux:subheading>

                    <flux:badge as="button" rounded color="zinc" icon="plus" size="lg">{{ __('Amount') }}</flux:badge>
                    <flux:badge as="button" rounded color="zinc" icon="plus" size="lg" class="max-md:hidden">{{ __('Status') }}</flux:badge>
                    <flux:badge as="button" rounded color="zinc" icon="plus" size="lg">{{ __('More filters...') }}</flux:badge>
                </div>
            </div>

            <flux:tabs variant="segmented" class="w-auto! ml-2" size="sm">
                <flux:tab icon="list-bullet" icon:variant="outline" />
                <flux:tab icon="squares-2x2" icon:variant="outline" />
            </flux:tabs>
        </div>

        <div class="flex gap-6 mb-6">
            @foreach ($this->stats as $stat)
                <div class="relative flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700 {{ $loop->iteration > 1 ? 'max-md:hidden' : '' }}  {{ $loop->iteration > 3 ? 'max-lg:hidden' : '' }}">
                    <flux:subheading>{{ $stat['title'] }}</flux:subheading>

                    <flux:heading size="xl" class="mb-2">{{ $stat['value'] }}</flux:heading>

                    <div class="flex items-center gap-1 font-medium text-sm @if ($stat['trendUp']) text-green-600 dark:text-green-400 @else text-red-500 dark:text-red-400 @endif">
                        <flux:icon :icon="$stat['trendUp'] ? 'arrow-trending-up' : 'arrow-trending-down'" variant="micro" /> {{ $stat['trend'] }}
                    </div>

                    <div class="absolute top-0 right-0 pr-2 pt-2">
                        <flux:button icon="ellipsis-horizontal" variant="subtle" size="sm" />
                    </div>
                </div>
            @endforeach
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column></flux:table.column>
                <flux:table.column class="max-md:hidden">{{ __('ID') }}</flux:table.column>
                <flux:table.column class="max-md:hidden">{{ __('Date') }}</flux:table.column>
                <flux:table.column class="max-md:hidden">{{ __('Status') }}</flux:table.column>
                <flux:table.column><span class="max-md:hidden">Customer</span><div class="md:hidden w-6"></div></flux:table.column>
                <flux:table.column>{{ __('Purchase') }}</flux:table.column>
                <flux:table.column>{{ __('Revenue') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->rows as $row)
                    <flux:table.row>
                        <flux:table.cell class="pr-2"><flux:checkbox /></flux:table.cell>
                        <flux:table.cell class="max-md:hidden">#{{ $row['id'] }}</flux:table.cell>
                        <flux:table.cell class="max-md:hidden">{{ $row['date'] }}</flux:table.cell>
                        <flux:table.cell class="max-md:hidden"><flux:badge :color="$row['status_color']" size="sm" inset="top bottom">{{ $row['status'] }}</flux:badge></flux:table.cell>
                        <flux:table.cell class="min-w-6">
                            <div class="flex items-center gap-2">
                                <flux:avatar src="https://i.pravatar.cc/48?img={{ $loop->index }}" size="xs" />
                                <span class="max-md:hidden">{{ $row['customer'] }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-6 truncate">{{ $row['purchase'] }}</flux:table.cell>
                        <flux:table.cell class="" variant="strong">{{ $row['amount'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end" offset="-15">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item icon="document-text">{{ __('View invoice') }}</flux:menu.item>
                                    <flux:menu.item icon="receipt-refund">{{ __('Refund') }}</flux:menu.item>
                                    <flux:menu.item icon="archive-box" variant="danger">{{ __('Archive') }}</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <flux:pagination :paginator="$this->paginator" />
    </flux:main>
</div>

<!--
    use \Livewire\WithPagination;

    #[\Livewire\Attributes\Computed]
    public function paginator()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(items: range(1, 50), total: 100, perPage: 10, currentPage: 1);
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            [
                'title' => 'Total revenue',
                'value' => '$38,393.12',
                'trend' => '16.2%',
                'trendUp' => true
            ],
            [
                'title' => 'Total transactions',
                'value' => '428',
                'trend' => '12.4%',
                'trendUp' => false
            ],
            [
                'title' => 'Total customers',
                'value' => '376',
                'trend' => '12.6%',
                'trendUp' => true
            ],
            [
                'title' => 'Average order value',
                'value' => '$87.12',
                'trend' => '13.7%',
                'trendUp' => true
            ]
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function rows()
    {
        return \App\Models\Order::all();
    }
-->

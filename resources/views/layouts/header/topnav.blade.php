<!-- @php
    $currentSection = Request::segment(1);
    $activeLocationName = collect(session()->get('all_locations', []))->get(session()->get('location_id'), 'Current Store');
    $companyOverview = $currentSection == 'dashboard' && !request()->query('location_scope');
    $navigation = [
        ['key' => 'dashboard', 'label' => 'Store Dashboard', 'icon' => 'fa-th-large', 'url' => url('/dashboard?location_scope='.session()->get('location_id')), 'sections' => ['dashboard']],
        ['key' => 'sales', 'label' => 'Weekly Sales', 'icon' => 'fa-usd', 'url' => url('/salesection'), 'sections' => ['salesection']],
        ['key' => 'labor', 'label' => 'Labor Reports', 'icon' => 'fa-users', 'url' => url('/laboursection'), 'sections' => ['laboursection', 'history']],
        ['key' => 'orders', 'label' => 'Supply Orders', 'icon' => 'fa-shopping-bag', 'url' => url('/order'), 'sections' => ['order']],
        ['key' => 'problems', 'label' => 'Problem Reports', 'icon' => 'fa-exclamation-triangle', 'url' => url('/report'), 'sections' => ['report']],
        ['key' => 'claims', 'label' => 'Claim Forms', 'icon' => 'fa-file-text-o', 'url' => url('/reimbursement'), 'sections' => ['reimbursement']],
        ['key' => 'communications', 'label' => 'Communications', 'icon' => 'fa-comment-o', 'url' => url('/service'), 'sections' => ['service']],
        ['key' => 'payroll', 'label' => 'Payroll History', 'icon' => 'fa-history', 'url' => url('/payrollemployee'), 'sections' => ['payrollemployee']],
        ['key' => 'reports', 'label' => 'Reports & Analytics', 'icon' => 'fa-bar-chart', 'url' => url('/reportingsection'), 'sections' => ['reportingsection']],
    ];
@endphp -->

@php
    $currentSection = Request::segment(1);

    // Settings pages use an explicit company flag when opened from the
    // company dashboard; store settings keep their selected location context.
    $isCompanySettingsPage = in_array(Auth::user()->role, ['Owner', 'Admin'])
        && request()->query('company') == '1'
        && in_array($currentSection, ['location', 'user', 'product', 'seller', 'vendor', 'category']);
    $showCompanySettingsTabs = in_array(Auth::user()->role, ['Owner', 'Admin'])
        && ($isCompanySettingsPage || ($currentSection == 'dashboard' && !request()->query('location_scope')));
    $companyOverview = ($currentSection == 'dashboard' && !request()->query('location_scope'))
        || $isCompanySettingsPage;
    $companySettingsNavigation = [
        ['label' => 'Company Dashboard', 'icon' => 'fa-building-o', 'url' => url('/dashboard'), 'sections' => ['dashboard']],
        ['label' => 'Locations', 'icon' => 'fa-map-marker', 'url' => url('/location?company=1'), 'sections' => ['location']],
        ['label' => 'Users', 'icon' => 'fa-users', 'url' => url('/user?company=1'), 'sections' => ['user']],
        ['label' => 'Products', 'icon' => 'fa-cube', 'url' => url('/product?company=1'), 'sections' => ['product']],
        ['label' => 'Vendors', 'icon' => 'fa-truck', 'url' => url('/seller?company=1'), 'sections' => ['seller', 'vendor']],
        ['label' => 'Categories', 'icon' => 'fa-tags', 'url' => url('/category?company=1'), 'sections' => ['category']],
    ];

    $navigation = [
        [
            'key' => 'dashboard',
            'label' => 'Store Dashboard',
            'icon' => 'fa-th-large',
            'url' => url('/dashboard?location_scope=' . session()->get('location_id')),
            'sections' => ['dashboard']
        ],
        [
            'key' => 'sales',
            'label' => 'Weekly Sales',
            'icon' => 'fa-usd',
            'url' => url('/salesection'),
            'sections' => ['salesection']
        ],
        [
            'key' => 'labor',
            'label' => 'Labor Reports',
            'icon' => 'fa-users',
            'url' => url('/laboursection'),
            'sections' => ['laboursection', 'history']
        ],

        /*
        [
            'key' => 'orders',
            'label' => 'Supply Orders',
            'icon' => 'fa-shopping-bag',
            'url' => url('/order'),
            'sections' => ['order']
        ],
        [
            'key' => 'problems',
            'label' => 'Problem Reports',
            'icon' => 'fa-exclamation-triangle',
            'url' => url('/report'),
            'sections' => ['report']
        ],
        */

        [
            'key' => 'claims',
            'label' => 'Claim Forms',
            'icon' => 'fa-file-text-o',
            'url' => url('/reimbursement'),
            'sections' => ['reimbursement']
        ],

        /*
        [
            'key' => 'communications',
            'label' => 'Communications',
            'icon' => 'fa-comment-o',
            'url' => url('/service'),
            'sections' => ['service']
        ],
        [
            'key' => 'payroll',
            'label' => 'Payroll History',
            'icon' => 'fa-history',
            'url' => url('/payrollemployee'),
            'sections' => ['payrollemployee']
        ],
        */

        [
            'key' => 'reports',
            'label' => 'Reports & Analytics',
            'icon' => 'fa-bar-chart',
            'url' => url('/reportingsection'),
            'sections' => ['reportingsection']
        ],
    ];
@endphp

<header class="kv-topbar">
    <div class="kv-topbar-inner">
        <a class="kv-brand" href="{{ url('/dashboard') }}">
            <span class="kv-brand-mark">KV</span>
            <span class="kv-brand-copy">
                <strong>KV MANAGEMENT <small>LLC</small></strong>
                <span>Dry Cleaning Franchise Operations</span>
            </span>
        </a>

        <div class="kv-topbar-right">
            <form class="kv-location" method="POST" action="{{ url('/setlocation') }}" aria-label="Change current location">
                {{ csrf_field() }}
                <i class="fa fa-building-o"></i>
                <label class="sr-only" for="kvCurrentLocation">Current location</label>
                <span class="kv-location-label">LOCATION:</span>
                <select class="kv-location-select" id="kvCurrentLocation" name="location" onchange="if (this.value === 'all') { window.location.href = '{{ url('/dashboard') }}'; } else { if ('{{ Request::segment(1) }}' === 'dashboard' || '{{ $isCompanySettingsPage ? '1' : '0' }}' === '1') { this.form.action = '{{ url('/setlocation') }}?dashboard=1'; } this.form.submit(); }">
                    <option value="all" {{ $companyOverview ? 'selected' : '' }}>All Locations (Company Overview)</option>
                    @foreach(session()->get('all_locations', []) as $locationId => $locationName)
                        <option value="{{ $locationId }}" {{ ($currentSection == 'dashboard' && request()->query('location_scope') == $locationId) || (!$companyOverview && (string) session()->get('location_id') === (string) $locationId) ? 'selected' : '' }}>{{ $locationName }}</option>
                    @endforeach
                </select>
            </form>
            <div class="dropdown kv-notifications">
                <button class="kv-icon-button dropdown-toggle" type="button" id="kvNotifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
                    <i class="fa fa-bell-o"></i>
                    @if(session()->get('total_new_csr') > 0)<span class="kv-notification-count">{{ session()->get('total_new_csr') }}</span>@endif
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="kvNotifications">
                    @if(session()->get('all_new_csr_data') != null)
                        @foreach(session()->get('all_new_csr_data') as $data)
                            @php $datafill = "Follow up with $data->customer_name on $data->followup_date by $data->followup_time"; @endphp
                            <li>{{ link_to_route('service.edit', $datafill, [$data->id]) }}</li>
                        @endforeach
                    @else
                        <li><a href="#">No notifications available.</a></li>
                    @endif
                </ul>
            </div>
            <div class="dropdown kv-user-menu">
                <button class="kv-user-button dropdown-toggle" type="button" id="kvUserMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="kv-avatar"><i class="fa fa-user"></i></span>
                    <span><strong>{{ Auth::user()->name }}</strong><small><i class="fa fa-shield"></i> {{ Auth::user()->role }}</small></span>
                    <i class="fa fa-chevron-down kv-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="kvUserMenu">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" style="background:none;border:0;color:#17263b;display:block;padding:3px 20px;text-align:left;width:100%;">
                                <i class="fa fa-sign-out"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

@if(!$companyOverview || in_array(Auth::user()->role, ['Owner', 'Admin']))
<nav class="kv-tabbar" aria-label="Main navigation">
    <div class="kv-tabbar-inner">
        <div class="kv-tab-links">
        @if($showCompanySettingsTabs)
            <a class="kv-tab kv-tab-dashboard {{ $currentSection == 'dashboard' ? 'is-active' : '' }}" href="{{ url('/dashboard') }}">
                <i class="fa fa-building-o"></i><span>Company Dashboard</span>
            </a>
            @php $companySettingsSections = ['location', 'user', 'product', 'seller', 'vendor', 'category']; @endphp
            <div class="dropdown kv-settings-nav {{ in_array($currentSection, $companySettingsSections) ? 'is-active' : '' }} kv-settings-nav-company">
                <button class="kv-tab dropdown-toggle {{ in_array($currentSection, $companySettingsSections) ? 'is-active' : '' }}" type="button" id="kvSettingsMenu" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-cog"></i><span>Settings</span><i class="fa fa-chevron-down kv-settings-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right kv-settings-menu" aria-labelledby="kvSettingsMenu">
                    <li class="kv-settings-heading">Company settings</li>
                    @foreach(array_slice($companySettingsNavigation, 1) as $item)
                        <li class="{{ in_array($currentSection, $item['sections']) ? 'active' : '' }}"><a href="{{ $item['url'] }}"><i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @else
            @foreach($navigation as $item)
                <a class="kv-tab kv-tab-dashboard {{ in_array($currentSection, $item['sections']) ? 'is-active' : '' }}" href="{{ $item['url'] }}">
                    <i class="fa {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @if(in_array(Auth::user()->role, ['Owner', 'Admin']))
            @php $settingsSections = ['location', 'user', 'menu', 'category', 'product', 'seller', 'vendor', 'sale', 'discount', 'adjustment', 'receipt', 'payrollcategory', 'payrolldepartment', 'payrollemployee', 'asign_loaction']; @endphp
            <div class="dropdown kv-settings-nav {{ in_array($currentSection, $settingsSections) ? 'is-active' : '' }} {{ $companyOverview ? 'kv-settings-nav-company' : '' }}">
                <button class="kv-tab dropdown-toggle {{ in_array($currentSection, $settingsSections) ? 'is-active' : '' }}" type="button" id="kvSettingsMenu" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-cog"></i><span>Settings</span><i class="fa fa-chevron-down kv-settings-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right kv-settings-menu" aria-labelledby="kvSettingsMenu">
                    <li class="kv-settings-heading">Administration</li>
                    <li class="{{ $currentSection == 'location' ? 'active' : '' }}"><a href="{{ url('/location?location_scope=' . session()->get('location_id')) }}"><i class="fa fa-map-marker"></i> Locations</a></li>
                    <li class="{{ $currentSection == 'user' ? 'active' : '' }}"><a href="{{ url('/user?location_scope=' . session()->get('location_id')) }}"><i class="fa fa-users"></i> Users</a></li>
                    <li class="{{ $currentSection == 'asign_loaction' ? 'active' : '' }}"><a href="{{ url('/asign_loaction') }}"><i class="fa fa-th-list"></i> Menu assignments</a></li>
                    <li class="kv-settings-heading">Catalog</li>
                    <li class="{{ $currentSection == 'product' ? 'active' : '' }}"><a href="{{ url('/product?location_scope=' . session()->get('location_id')) }}"><i class="fa fa-cube"></i> Products</a></li>
                    <li class="{{ in_array($currentSection, ['seller', 'vendor']) ? 'active' : '' }}"><a href="{{ url('/seller?location_scope=' . session()->get('location_id')) }}"><i class="fa fa-truck"></i> Vendors</a></li>
                    <li class="{{ $currentSection == 'category' ? 'active' : '' }}"><a href="{{ url('/category?location_scope=' . session()->get('location_id')) }}"><i class="fa fa-tags"></i> Categories</a></li>
                    <li class="kv-settings-heading">Sales setup</li>
                    <li class="{{ $currentSection == 'discount' ? 'active' : '' }}"><a href="{{ url('/discount') }}"><i class="fa fa-tags"></i> Discounts &amp; Credits</a></li>
                    <li class="{{ $currentSection == 'adjustment' ? 'active' : '' }}"><a href="{{ url('/adjustment') }}"><i class="fa fa-sliders"></i> Adjustments</a></li>
                    <li class="{{ $currentSection == 'receipt' ? 'active' : '' }}"><a href="{{ url('/receipt') }}"><i class="fa fa-credit-card"></i> Receipts</a></li>
                    <li class="{{ $currentSection == 'sale' ? 'active' : '' }}"><a href="{{ url('/sale') }}"><i class="fa fa-usd"></i> Sales</a></li>
                    <li class="kv-settings-heading">Payroll setup</li>
                    <li class="{{ $currentSection == 'payrollcategory' ? 'active' : '' }}"><a href="{{ url('/payrollcategory') }}"><i class="fa fa-list"></i> Payroll categories</a></li>
                    <li class="{{ $currentSection == 'payrolldepartment' ? 'active' : '' }}"><a href="{{ url('/payrolldepartment') }}"><i class="fa fa-sitemap"></i> Payroll departments</a></li>
                    <li class="{{ $currentSection == 'payrollemployee' ? 'active' : '' }}"><a href="{{ url('/payrollemployee') }}"><i class="fa fa-users"></i> Payroll employees</a></li>
                </ul>
            </div>
        @endif
        @endif
        </div>
        <div class="kv-nav-context-wrap">
            <div class="kv-nav-context" aria-label="Current store">
                <i class="fa fa-map-marker"></i>
                <span>{{ $companyOverview ? 'Company Overview' : $activeLocationName }}</span>
            </div>
        </div>
    </div>
</nav>
@endif
<script>
(function () {
    var settings = document.querySelector('.kv-settings-nav');
    var toggle = document.getElementById('kvSettingsMenu');
    if (!settings || !toggle) return;
    toggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var open = settings.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (event) {
        if (!settings.contains(event.target)) {
            settings.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
}());
</script>

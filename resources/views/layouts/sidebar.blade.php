<div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
    <ul class="nav nav-pills flex-column mb-sm-auto mb-0" id="menu">
        
        <li>
            @if(auth()->user()->is_admin === 1)
                <a href="{{ route('users.index') }}" class="nav-link px-0 align-middle text-white {{ Request::routeIs('users.*') ? 'active' : '' }}">
                    <i class="fs-5 bi bi-person"></i> <span class="ms-1 d-none d-sm-inline">Users</span>
                </a>
            @endif
        </li>

        <!-- Menu for Inquiries -->

        <li class="nav-item">
            <a href="#" class="nav-link align-middle px-0 text-white {{ Request::routeIs('inquiries.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#inquiriesSubMenu" aria-expanded="false">
                <i class="fs-5 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Inquiries</span>
            </a>
            
            <!-- Sub-menu for Inquiries -->
            <ul class="collapse" id="inquiriesSubMenu">
                <li>
                    <a href="#" class="nav-link px-0 text-white ms-2" data-bs-toggle="collapse" data-bs-target="#domesticsubSubMenu" aria-expanded="false">
                        <i class="fs-5 bi-envelope"></i> <span class="ms-1 d-none d-sm-inline">Domestic Inquiries</span>
                    </a>
                    
                    <!-- Sub-sub-menu for Domestic Inquiries -->
                    <ul class="collapse" id="domesticsubSubMenu">
                        <li>
                            <a href="{{ route('inquiries.create') }}" class="nav-link px-0 text-white ms-3">
                                <i class="fs-5 bi-file-earmark"></i> <span class="ms-1 d-none d-sm-inline">New Inquiries</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('inquiries.index') }}" class="nav-link px-0 text-white ms-3">
                                <i class="fs-5 bi-question-circle"></i> <span class="ms-1 d-none d-sm-inline">All Inquiries</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
             <!-- Sub-menu for Inquiries -->
            <ul class="collapse" id="inquiriesSubMenu">
                <li>
                    <a href="#" class="nav-link px-0 text-white ms-2" data-bs-toggle="collapse" data-bs-target="#internationalsubSubMenu" aria-expanded="false">
                        <i class="fs-5 bi-envelope"></i> <span class="ms-1 d-none d-sm-inline">International Inquiries</span>
                    </a>
                    
                    <!-- Sub-sub-menu for Support Inquiries -->
                    <ul class="collapse" id="internationalsubSubMenu">
                        <li>
                            <a href="{{ route('international_inquiries.create') }}" class="nav-link px-0 text-white ms-3">
                                <i class="fs-5 bi-file-earmark"></i> <span class="ms-1 d-none d-sm-inline">New Inquiries</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('international_inquiries.index') }}" class="nav-link px-0 text-white ms-3">
                                <i class="fs-5 bi-question-circle"></i> <span class="ms-1 d-none d-sm-inline">All Inquiries</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

         <!-- Menu for Offers -->

        <li class="nav-item">
            <a href="#" class="nav-link align-middle px-0 text-white {{ Request::routeIs('inquiry.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#offersSubMenu" aria-expanded="false">
                <i class="fs-5 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Offers</span>
            </a>
            
            <!-- Sub-menu for Inquiries -->
            <ul class="collapse" id="offersSubMenu">
                
                <li>
                    <a href="{{route('inquiry.approved.offers')}}" class="nav-link px-0 text-white ms-3">
                        <i class="fs-5 bi-file-earmark"></i> <span class="ms-1 d-none d-sm-inline">Domestic Offers </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('inquiry.international.approved.offers')}}" class="nav-link px-0 text-white ms-3">
                        <i class="fs-5 bi-question-circle"></i> <span class="ms-1 d-none d-sm-inline">International Offers</span>
                    </a>
                </li>
                    
            </ul>

            <!-- Sub-menu for Cancellations -->
            <a href="#" class="nav-link align-middle px-0 text-white" data-bs-toggle="collapse" data-bs-target="#CancellationsSubMenu" aria-expanded="false">
                <i class="fs-5 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Cancellations</span>
            </a>
            <ul class="collapse" id="CancellationsSubMenu">
                
                <li>
                    <a href="{{route('inquiry.cancellation.offers')}}" class="nav-link px-0 text-white ms-3">
                        <i class="fs-5 bi-file-earmark"></i> <span class="ms-1 d-none d-sm-inline">Domestic Inc </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('inquiry.international.cancellation.offers')}}" class="nav-link px-0 text-white ms-3">
                        <i class="fs-5 bi-question-circle"></i> <span class="ms-1 d-none d-sm-inline">International Inc</span>
                    </a>
                </li>
                    
            </ul>
        </li>
        
    </ul>
    <hr>
</div>
   
    

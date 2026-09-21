 <!--start sidebar -->
 <aside class="sidebar-wrapper" data-simplebar="true">
     <div class="sidebar-header">
         <div>
             <img src="{{ asset('assets/images/logo-icon-2.png') }}" class="logo-icon" alt="logo icon">
         </div>
         <div>
             <h4 class="logo-text">SYN-UI</h4>
         </div>
         <div class="toggle-icon ms-auto"><ion-icon name="menu-sharp"></ion-icon>
         </div>
     </div>
     <!--navigation-->
     <ul class="metismenu" id="menu">
         <li>
             <a href="{{ route('dashboard') }}">
                 <div class="parent-icon"><ion-icon name="home-sharp"></ion-icon>
                 </div>
                 <div class="menu-title">Dashboard</div>
             </a>
         </li>
         <li class="menu-label">Patients</li>
         <li>
             <a href="{{ route('patient.list-patient') }}">
                 <div class="parent-icon"><i class="lni lni-user"></i>

                 </div>
                 <div class="menu-title">Patients</div>
             </a>
         </li>

         <li>
             <a href="{{ route('appointment.list-appointment') }}">
                 <div class="parent-icon"><i class="lni lni-calendar"></i>
                 </div>
                 <div class="menu-title">Appointment</div>
             </a>
         </li>
         <li>
             <a href="{{ route('patient-bill.list-bill') }}">
                 <div class="parent-icon"><i class="lni lni-credit-card" style="font-size: 22px;"><ion-icon
                             name="cash-outline"></ion-icon></i>
                 </div>
                 <div class="menu-title">Patient Billing</div>
             </a>
         </li>
         <li class="menu-label">Faculties</li>
         <li>
             <a href="{{ route('faculties.list-faculties') }}">
                 <div class="parent-icon"><i class="fadeIn animated bx bx-group"></i>
                 </div>
                 <div class="menu-title">Faculties</div>
             </a>
         </li>
         {{-- <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><ion-icon name="server-sharp"></ion-icon>
                </div>
                <div class="menu-title">Prerequisites</div>
            </a>
            <ul>
                <li> <a href="{{ route('prerequisite.list-department') }}"><i
                            class="fadeIn animated bx bx-chevrons-right"></i>Department</a>
                </li>
                <li> <a href="{{ route('prerequisite.list-source') }}"><i
                            class="fadeIn animated bx bx-chevrons-right"></i>Source</a>
                </li>
                <li> <a href="{{ route('prerequisite.list-services') }}"><i
                            class="fadeIn animated bx bx-chevrons-right"></i>Services</a>
                </li>
            </ul>
        </li> --}}
         <li class="menu-label">Inventory</li>
         <li>
             <a href="{{ route('product.list-product') }}">
                 <div class="parent-icon"><ion-icon name="basket-outline"></ion-icon>
                 </div>
                 <div class="menu-title">Products </div>
             </a>
         </li>
         <li>
             <a class="has-arrow" href="javascript:;">
                 <div class="parent-icon"><ion-icon name="cart-sharp"></ion-icon>
                 </div>
                 <div class="menu-title">Purchase</div>
             </a>
             <ul>
                 <li> <a href="{{ route('bill.list-bill') }}"><ion-icon name="ellipse-outline"></ion-icon>Purchase
                         Bills</a>
                 </li>
             </ul>
         </li>
         <li>
             <a class="has-arrow" href="javascript:;">
                 <div class="parent-icon"><ion-icon name="cash-outline"></ion-icon>
                 </div>
                 <div class="menu-title">Sales</div>
             </a>
             <ul>
                 <li> <a href="{{ route('patient-invoice.list-invoice') }}"><ion-icon
                             name="ellipse-outline"></ion-icon>Invoices</a>
                 </li>
             </ul>
         </li>
         {{-- <li class="menu-label">Task Management</li>
             </li>
        <li>
            <a href="{{ route('prerequisite.list-source') }}">
                <div class="parent-icon"><ion-icon name="person-circle-sharp"></ion-icon>
                </div>
                <div class="menu-title">Source</div>
            </a>
        </li>
        <li>
            <a href="{{ route('prerequisite.list-services') }}">
                <div class="parent-icon"><ion-icon name="create-sharp"></ion-icon>
                </div>
                <div class="menu-title">Services</div>
            </a>
        </li>
       <li class="menu-label">Others</li>
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><ion-icon name="list-sharp"></ion-icon>
                </div>
                <div class="menu-title">Menu Levels</div>
            </a>
            <ul>
                <li> <a class="has-arrow" href="javascript:;"><i class="fadeIn animated bx bx-chevrons-right"></i>Level
                        One</a>
                    <ul>
                        <li> <a class="has-arrow" href="javascript:;"><ion-icon name="ellipse-outline"></ion-icon>Level
                                Two</a>
                            <ul>
                                <li> <a href="javascript:;"><i class="fadeIn animated bx bx-chevrons-right"></i>Level
                                        Three</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:;" target="_blank">
                <div class="parent-icon"><ion-icon name="document-text-sharp"></ion-icon>
                </div>
                <div class="menu-title">Documentation</div>
            </a>
        </li>
        <li>
            <a href="https://themeforest.net/user/codervent" target="_blank">
                <div class="parent-icon"><ion-icon name="link-sharp"></ion-icon>
                </div>
                <div class="menu-title">Support</div>
            </a>
        </li> --}}
     </ul>
     <!--end navigation-->
 </aside>
 <!--end sidebar -->

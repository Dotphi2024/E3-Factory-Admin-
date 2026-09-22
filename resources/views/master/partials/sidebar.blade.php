 <!--start sidebar -->
 <aside class="sidebar-wrapper" data-simplebar="true">
     <div class="sidebar-header">
         <div>
             <img src="{{ asset('assets/images/logo2.png') }}" class="logo-icon" alt="logo icon">
         </div>
         <div>
             <h4 class="logo-text">E3</h4>
         </div>
         <div class="toggle-icon ms-auto"><ion-icon name="menu-sharp"></ion-icon>
         </div>
     </div>
     <!--navigation-->
     <ul class="metismenu" id="menu">
         <li>
             <a
                 href="{{ Auth::user()->user_type == 'entry-user' ? route('entry-user-dashboard') : route('master.dashboard') }}">
                 <div class="parent-icon"><ion-icon name="home-sharp"></ion-icon>
                 </div>
                 <div class="menu-title">Dashboard</div>
             </a>
         </li>
         @if (Auth::user()->user_type !== 'entry-user')
             <li class="menu-label">Settings</li>
         @endif
         @can('roles list')
             <li>
                 <a href="{{ route('master.roles.list') }}">
                     <div class="parent-icon"><ion-icon name="people-circle-sharp"></ion-icon>
                     </div>
                     <div class="menu-title">Roles and Permission</div>
                 </a>
             </li>
         @endcan
         @can('users list')
             <li>
                 <a href="{{ route('master.users.list') }}">
                     <div class="parent-icon"><ion-icon name="person-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Users</div>
                 </a>
             </li>
         @endcan
         @can('settings list')
             <li>
                 <a class="has-arrow" href="javascript:;">
                     <div class="parent-icon"><ion-icon name="settings-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Settings</div>
                 </a>
                 <ul>
                     <li>
                         <a href="{{ route('master.setting.privacy-policy') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Privacy Policy</a>

                     </li>
                     <li>
                         <a href="{{ route('master.setting.terms-of-service') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Terms of Service</a>

                     </li>
                     <li>
                         <a href="{{ route('master.setting.learn-about') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Learn About</a>

                     </li>
                     <li>
                         <a href="{{ route('master.setting.get-help') }}"><ion-icon name="ellipse-outline"></ion-icon>Get
                             Help</a>

                     </li>
                     <li>
                         <a href="{{ route('master.setting.recommendation-banner') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Recommendation Banner</a>

                     </li>
                 </ul>
             </li>
         @endcan
         @if (Auth::user()->user_type !== 'entry-user')
             <li class="menu-label">Operations</li>
         @endif
         @can('recommendations list')
             <li>
                 <a class="has-arrow" href="javascript:;">
                     <div class="parent-icon"><ion-icon name="people-circle-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Leads</div>
                 </a>
                 <ul>
                     <li>
                         <a href="{{ route('master.recommendations.list') }}?status=Pending"><ion-icon
                                 name="ellipse-outline"></ion-icon>Pending</a>

                     </li>
                     <li>
                         <a href="{{ route('master.recommendations.list') }}?status=InProgress"><ion-icon
                                 name="ellipse-outline"></ion-icon>In Progress</a>

                     </li>
                     <li>
                         <a href="{{ route('master.recommendations.list') }}?status=Completed"><ion-icon
                                 name="ellipse-outline"></ion-icon>Completed</a>

                     </li>
                     <li>
                         <a href="{{ route('master.recommendations.list') }}?status=Failed"><ion-icon
                                 name="ellipse-outline"></ion-icon>Failed</a>

                     </li>
                     <li>
                         <a href="{{ route('master.recommendations.follow-up-list') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Follow Ups</a>

                     </li>
                 </ul>
             </li>
         @endcan
         @can('course list')
             <li>
                 <a href="{{ route('master.courses.list') }}">
                     <div class="parent-icon"><ion-icon name="laptop-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Programs</div>
                 </a>
             </li>
         @endcan
         @can('batch list')
             <li>
                 <a href="{{ route('master.batches.list') }}">
                     <div class="parent-icon"><ion-icon name="people-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Batches</div>
                 </a>
             </li>
         @endcan
         @can('participant list')
             <li>
                 <a href="{{ route('master.participants.list') }}">
                     <div class="parent-icon"><ion-icon name="person-circle-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Participants</div>
                 </a>
             </li>
             <li>
                 <a href="{{ route('master.coaches.list') }}">
                     <div class="parent-icon"><ion-icon name="accessibility-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Coaches</div>
                 </a>
             </li>
         @endcan
         <li>
             <a href="{{ route('master.value-posts.list') }}">
                 <div class="parent-icon"><ion-icon name="megaphone-outline"></ion-icon>
                 </div>
                 <div class="menu-title">Value Post</div>
             </a>
         </li>
         @can('messages list')
             <li>
                 <a class="has-arrow" href="javascript:;">
                     <div class="parent-icon"><ion-icon name="chatbox-ellipses-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Messages</div>
                 </a>
                 <ul>
                     <li>
                         <a href="{{ route('master.messages.batch-messages') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Batch Messages</a>
                     </li>
                     <li>
                         <a href="{{ route('master.messages.group-messages') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Group Messages</a>
                     </li>
                     <li>
                         <a href="{{ route('master.messages.participant-messages') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>Particant Messages</a>
                     </li>
                 </ul>
             </li>
         @endcan
         @if (Auth::user()->user_type !== 'entry-user')
            <li class="menu-label">Content Managements</li>
         @endif
         @can('photo-gallery list')
             <li>
                 <a href="{{ route('master.photo-gallery.list') }}">
                     <div class="parent-icon"><ion-icon name="images-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Photo Gallery</div>
                 </a>
             </li>
         @endcan
         @can('video-gallery list')
             <li>
                 <a href="{{ route('master.video-gallery.list') }}">
                     <div class="parent-icon"><ion-icon name="videocam-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Video Gallery</div>
                 </a>
             </li>
         @endcan
         @can('notice-announcement list')
             <li>
                 <a href="{{ route('master.notice-and-announcement.list') }}">
                     <div class="parent-icon"><ion-icon name="notifications-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Notice & Announcement</div>
                 </a>
             </li>
         @endcan
         @can('e3-talk list')
             <li>
                 <a href="{{ route('master.e3-talk.list') }}">
                     <div class="parent-icon"><ion-icon name="videocam-outline"></ion-icon>
                     </div>
                     <div class="menu-title">E3 Talk</div>
                 </a>
             </li>
         @endcan
         @can('notice-announcement list')
             <li>
                 <a href="{{ route('master.testimonials.list') }}">
                     <div class="parent-icon"><ion-icon name="aperture-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Testimonials</div>
                 </a>
             </li>
         @endcan
         @can('guest-homepage list')
             <li>
                 <a class="has-arrow" href="javascript:;">
                     <div class="parent-icon"><ion-icon name="home-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Guest Homepage</div>
                 </a>
                 <ul>
                     <li>
                         <a href="{{ route('master.guest-homepage.list') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>List</a>

                     </li>
                     <li>
                         <a href="{{ route('master.guest-homepage.add') }}?title=1&description=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Title & Description</a>

                     </li>
                     <li>
                         <a href="{{ route('master.guest-homepage.add') }}?title=1&description=1&image=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Title, Description & Image</a>

                     </li>
                     <li>
                         <a href="{{ route('master.guest-homepage.add') }}?image=1&url=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Image & Url</a>

                     </li>
                     <li>
                         <a href="{{ route('master.guest-homepage.add') }}?video_url=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Video Url</a>

                     </li>

                 </ul>
             </li>
         @endcan
         @can('batch-homepage list')
             <li>
                 <a class="has-arrow" href="javascript:;">
                     <div class="parent-icon"><ion-icon name="home-outline"></ion-icon>
                     </div>
                     <div class="menu-title">Batch Homepage</div>
                 </a>
                 <ul>
                     <li>
                         <a href="{{ route('master.batch-homepage.list') }}"><ion-icon
                                 name="ellipse-outline"></ion-icon>List</a>

                     </li>
                     <li>
                         <a href="{{ route('master.batch-homepage.add') }}?title=1&description=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Title & Description</a>

                     </li>
                     <li>
                         <a href="{{ route('master.batch-homepage.add') }}?title=1&description=1&image=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Title, Description & Image</a>

                     </li>
                     <li>
                         <a href="{{ route('master.batch-homepage.add') }}?image=1&url=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Image & Url</a>

                     </li>
                     <li>
                         <a href="{{ route('master.batch-homepage.add') }}?video_url=1"><ion-icon
                                 name="ellipse-outline"></ion-icon>Add Video Url</a>

                     </li>

                 </ul>
             </li>
         @endcan


     </ul>
     <!--end navigation-->
 </aside>
 <!--end sidebar -->

 <nav id="sidebar" class="sidebar js-sidebar">
     <div class="sidebar-content js-simplebar">
         <a class="sidebar-brand" href="dashboard.html">
             <img src="{{ asset('img/icons/golden_gym.png') }}" class="sidebar-logo" />
         </a>

         <ul class="sidebar-nav">
             <li class="sidebar-header">Páginas</li>

             <li class="sidebar-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.dashboard') }}">
                     <i class="align-middle" data-feather="sliders"></i>
                     <span class="align-middle">Panel principal</span>
                 </a>
             </li>

             <li class="sidebar-item {{ Route::is('admin.profile') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.profile') }}">
                     <i class="align-middle" data-feather="user"></i>
                     <span class="align-middle">Cuenta</span>
                 </a>
             </li>

             <li class="sidebar-item {{ Route::is('admin.usuarios') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.usuarios') }}">
                     <i class="align-middle" data-feather="users"></i>
                     <span class="align-middle">Usuarios</span>
                 </a>
             </li>

             <li class="sidebar-item">
                 <a
                     class="sidebar-link d-flex justify-content-between align-items-center {{ Request::routeIs('admin.resumen.*') ? '' : 'collapsed' }}"
                     data-bs-toggle="collapse"
                     href="#contabilidadMenu"
                     role="button"
                     aria-expanded="{{ Request::routeIs('admin.resumen.*') ? 'true' : 'false' }}"
                     aria-controls="contabilidadMenu">
                     <span>
                         <i class="align-middle" data-feather="dollar-sign"></i>
                         <span class="align-middle">Contabilidad</span>
                     </span>
                     <i data-feather="chevron-down" class="icon-sm"></i>
                 </a>

                 <div class="collapse {{ Request::routeIs('admin.resumen.*') ? 'show' : '' }}" id="contabilidadMenu">
                     <ul class="sidebar-nav ps-4">
                         <li class="sidebar-item {{ Route::is('admin.resumen.general') ? 'active' : '' }}">
                             <a class="sidebar-link" href="{{ route('admin.resumen.general') }}">
                                 <i class="align-middle" data-feather="pie-chart"></i>
                                 <span class="align-middle">Resumen General</span>
                             </a>
                         </li>
                         <li class="sidebar-item {{ Route::is('admin.resumen.membresias') ? 'active' : '' }}">
                             <a class="sidebar-link" href="{{ route('admin.resumen.membresias') }}">
                                 <i class="align-middle" data-feather="activity"></i>
                                 <span class="align-middle">Resu. Membresías</span>
                             </a>
                         </li>
                         <li class="sidebar-item {{ Route::is('admin.resumen.ventas') ? 'active' : '' }}">
                             <a class="sidebar-link" href="{{ route('admin.resumen.ventas') }}">
                                 <i class="align-middle" data-feather="shopping-bag"></i>
                                 <span class="align-middle">Resumen Ventas</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>



             <li class="sidebar-header">Componentes</li>

             <li class="sidebar-item {{ Route::is('admin.membresias') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.membresias') }}">
                     <i class="align-middle" data-feather="star"></i>
                     <span class="align-middle">Membresía</span>
                 </a>
             </li>

             <li class="sidebar-item {{ Route::is('admin.clientes') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.clientes') }}">
                     <i class="align-middle" data-feather="users"></i>
                     <span class="align-middle">Clientes</span>
                 </a>
             </li>

             <li class="sidebar-item {{ Route::is('admin.productos') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.productos') }}">
                     <i class="align-middle" data-feather="grid"></i>
                     <span class="align-middle">Productos</span>
                 </a>
             </li>
             <li class="sidebar-item">
                 <a
                     class="sidebar-link d-flex justify-content-between align-items-center {{ Request::routeIs('admin.lista_ventas') || Request::routeIs('admin.vender') ? '' : 'collapsed' }}"
                     data-bs-toggle="collapse"
                     href="#ventasMenu"
                     role="button"
                     aria-expanded="{{ Request::routeIs('admin.lista_ventas') || Request::routeIs('admin.vender') ? 'true' : 'false' }}"
                     aria-controls="ventasMenu">
                     <span>
                         <i class="align-middle" data-feather="shopping-cart"></i>
                         <span class="align-middle">Ventas</span>
                     </span>
                     <i data-feather="chevron-down" class="icon-sm"></i>
                 </a>

                 <div class="collapse {{ Request::routeIs('admin.lista_ventas') || Request::routeIs('admin.vender') ? 'show' : '' }}" id="ventasMenu">
                     <ul class="sidebar-nav ps-4">
                         <li class="sidebar-item {{ Route::is('admin.lista_ventas') ? 'active' : '' }}">
                             <a class="sidebar-link" href="{{ route('admin.lista_ventas') }}">
                                 <i class="align-middle" data-feather="list"></i>
                                 <span class="align-middle">Listado de Ventas</span>
                             </a>
                         </li>
                         <li class="sidebar-item {{ Route::is('admin.vender') ? 'active' : '' }}">
                             <a class="sidebar-link" href="{{ route('admin.vender') }}">
                                 <i class="align-middle" data-feather="bar-chart-2"></i>
                                 <span class="align-middle">Vender</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>


             <li class="sidebar-item {{ Route::is('admin.confirmacion_pagos') ? 'active' : '' }}">
                 <a class="sidebar-link" href="{{ route('admin.confirmacion_pagos') }}">
                     <i class="align-middle" data-feather="check-circle"></i>
                     <span class="align-middle">Confirmación de Pagos</span>
                 </a>
             </li>
         </ul>
     </div>
 </nav>

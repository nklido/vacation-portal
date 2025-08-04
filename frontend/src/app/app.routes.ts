import { Routes } from '@angular/router';
import { LoginComponent } from './core/auth/login/login.component';
import { RequestsComponent as EmployeeRequestsComponent } from './pages/employee/requests/requests.component';
import { RequestFormComponent } from './pages/employee/request-form/request-form.component';
import { PendingRequestsComponent } from './pages/manager/pending-requests/pending-requests.component';
import { UsersComponent } from './pages/manager/users/users.component';
import { UserFormComponent } from './pages/manager/user-form/user-form.component';
import {LayoutComponent} from './core/layouts/layout/layout.component';
import {LoginGuard} from './guards/login.guard';
import {AuthGuard} from './guards/auth.guard';
import {RoleGuard} from './guards/role.guard';

export const routes: Routes = [
  {
    path: 'login',
    component: LoginComponent,
    canActivate: [LoginGuard]
  },
  {
    path: 'manager',
    component: LayoutComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: {
      title: 'Manager Portal',
      role: 'manager',
      navLinks: [
        { label: 'Users', icon: 'people', path: 'users' },
        { label: 'Pending Requests', icon: 'event', path: 'pending-requests' }
      ]
    },
    children: [
      { path: 'users', component: UsersComponent },
      { path: 'pending-requests', component: PendingRequestsComponent },
      { path: 'users/new', component: UserFormComponent },
      { path: 'users/:id', component: UserFormComponent },
      { path: '', redirectTo: 'users', pathMatch: 'full' }
    ]
  },
  {
    path: 'employee',
    component: LayoutComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: {
      title: 'Employee Portal',
      role: 'employee',
      navLinks: [
        { label: 'My Requests', icon: 'assignment', path: 'requests' },
      ]
    },
    children: [
      { path: 'requests', component: EmployeeRequestsComponent },
      { path: 'requests/add', component: RequestFormComponent },
      { path: '', redirectTo: 'requests', pathMatch: 'full' }
    ]
  },
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: '**', redirectTo: '/login' }
];

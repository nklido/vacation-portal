import { Injectable } from '@angular/core';
import {ActivatedRouteSnapshot, CanActivate, CanActivateFn, Router} from '@angular/router';
import {AuthService} from '../core/auth/auth.service';

@Injectable({ providedIn: 'root' })
export class RoleGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const expectedRole = route.data['role'];
    const actualRole = this.auth.getUser()?.role_name;

    if (actualRole === expectedRole) {
      return true;
    }

    if (actualRole === 'manager') {
      this.router.navigate(['/manager']);
    } else if (actualRole === 'employee') {
      this.router.navigate(['/employee']);
    } else {
      this.router.navigate(['/login']);
    }

    return false;
  }
}

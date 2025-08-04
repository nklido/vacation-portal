import {Injectable} from '@angular/core';
import {CanActivate, Router} from '@angular/router';
import {AuthService} from '../core/auth/auth.service';

@Injectable({ providedIn: 'root' })
export class LoginGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(): boolean {
    if (this.auth.getUser()) {
      const role = this.auth.getUser()?.role_name;
      this.router.navigate([role === 'manager' ? '/manager' : '/employee']);
      return false;
    }
    return true;
  }
}

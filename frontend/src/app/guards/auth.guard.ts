import {CanActivate, CanActivateFn, Router} from '@angular/router';
import { AuthService } from '../core/auth/auth.service';
import {Injectable} from '@angular/core';

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
  constructor(private auth: AuthService, private router: Router) {}

  canActivate(): boolean {
    if (!this.auth.getAuthToken()) {
      this.router.navigate(['/login']);
      return false;
    }
    return true;
  }
}

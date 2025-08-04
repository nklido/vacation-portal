import {HttpInterceptorFn} from '@angular/common/http';
import {inject} from '@angular/core';
import {AuthService} from './auth.service';
import {Router} from '@angular/router';
import {catchError, EMPTY, throwError} from 'rxjs';

export const authInterceptor: HttpInterceptorFn = (req, next) => {

  const authService = inject(AuthService);
  const authToken = authService.getAuthToken();
  const router = inject(Router);

  if (req.url.endsWith('/login')) {
    return next(req);
  }

  if (!authToken) {
    router.navigate(['/login']);
    return EMPTY;
  }

  const requestWithAuth = req.clone({
    setHeaders: {
      Authorization: `Bearer ${authToken}`
    }
  });

  return next(requestWithAuth).pipe(
    catchError(err => {
      // if (err.status === 401) {
      //   authService.logout();
      //
      //   router.navigate(['/login']);
      // }
      return throwError(() => err)
    })
  )
};

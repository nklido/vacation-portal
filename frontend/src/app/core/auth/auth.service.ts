import {Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpHeaders} from '@angular/common/http';
import {catchError, tap, throwError} from 'rxjs';
import {User} from '../../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) {
  }

  login(email: string, password: string) {
    const body = {
      email,
      password
    };

    return this.http.post<{ token: string, user: User }>(`/api/login`, body).pipe(
      tap(response => {
        localStorage.setItem('sessionId', response.token);
        localStorage.setItem('user', JSON.stringify(response.user));
      })
    );
  }

  getAuthToken() {
    const token = localStorage.getItem('sessionId');
    return token ? token : null;
  }

  getUser(): User | null {
    const userJson = localStorage.getItem('user');
    return userJson ? JSON.parse(userJson) as User : null;
  }

  logout() {
    localStorage.removeItem('sessionId');
    localStorage.removeItem('user');
  }
}

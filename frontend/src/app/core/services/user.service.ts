import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {User} from '../../models/user.model';
@Injectable({
  providedIn: 'root'
})
export class UserService {
  private readonly baseUrl = '/api/users';

  constructor(private http: HttpClient) {}

  getAll(): Observable<User[]> {
    return this.http.get<User[]>(this.baseUrl);
  }

  getById(id: number): Observable<User> {
    return this.http.get<User>(`/api/users/${id}`);
  }

  create(user: Partial<User>): Observable<any> {
    return this.http.post('/api/users', user);
  }

  update(id: number, user: Partial<User>): Observable<any> {
    return this.http.patch(`/api/users/${id}`, user);
  }

  delete(id: number): Observable<any> {
    return this.http.delete(`/api/users/${id}`);
  }
}

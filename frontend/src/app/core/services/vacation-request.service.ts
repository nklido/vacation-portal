import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {VacationRequest} from '../../models/vacation-request.model';

@Injectable({
  providedIn: 'root'
})
export class VacationRequestService {
  private baseUrl = '/api/vacation-requests';

  constructor(private http: HttpClient) {}


  getPending(): Observable<VacationRequest[]> {
    return this.http.get<VacationRequest[]>(this.baseUrl);
  }

  getAll(): Observable<VacationRequest[]> {
    return this.http.get<VacationRequest[]>(this.baseUrl);
  }


  create(request: Partial<VacationRequest>): Observable<VacationRequest> {
    return this.http.post<VacationRequest>(this.baseUrl, request);
  }

  updateStatus(id: number, status: 'APPROVED' | 'REJECTED'): Observable<void> {
    return this.http.patch<void>(`${this.baseUrl}/${id}/status`, { status });
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}

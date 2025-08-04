import {User} from './user.model';

export interface VacationRequest {
  id: number;
  from_date: string;
  to_date: string;
  total_days: number;
  reason: string;
  employee: User;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  created_at: string;
}

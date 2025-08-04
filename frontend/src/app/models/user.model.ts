export interface User {
  id: number;
  name: string;
  email: string;
  employee_code: number;
  role_name: 'employee' | 'manager';
}

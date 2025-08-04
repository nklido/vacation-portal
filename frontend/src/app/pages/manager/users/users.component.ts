import { Component, OnInit } from '@angular/core';
import {UserService} from '../../../core/services/user.service';
import {User} from '../../../models/user.model';
import {MatCard, MatCardModule, MatCardTitle} from '@angular/material/card';
import {
  MatCell,
  MatCellDef,
  MatColumnDef,
  MatHeaderCell,
  MatHeaderCellDef, MatHeaderRow,
  MatRow,
  MatTable, MatTableModule
} from '@angular/material/table';
import {MatIcon, MatIconModule} from '@angular/material/icon';
import {RouterLink} from '@angular/router';
import {MatButton, MatIconButton} from '@angular/material/button';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  imports: [
    MatCardTitle,
    MatCard,
    MatHeaderCell,
    MatColumnDef,
    MatTable,
    MatCell,
    MatCellDef,
    MatHeaderCellDef,
    MatRow,
    MatHeaderRow,
    MatCardModule,
    MatTableModule,
    MatIcon,
    MatIconModule,
    RouterLink,
    MatButton,
    MatIconButton
  ],
  styleUrls: ['./users.component.scss']
})
export class UsersComponent implements OnInit {
  users: User[] = [];
  displayedColumns: string[] = ['id', 'name', 'email', 'employeeCode', 'role', 'actions'];

  constructor(private userService: UserService) {}

  ngOnInit(): void {
    this.userService.getAll().subscribe(users => {
      this.users = users;
    });
  }

deleteUser(id: number) {
  if (confirm('Are you sure you want to delete this user?')) {
    this.userService.delete(id).subscribe({
      next: () => {
        this.users = this.users.filter(u => u.id !== id);
      },
      error: err => {
        alert(err?.error?.message || 'Failed to delete user.');
      }
    });
  }
}


}

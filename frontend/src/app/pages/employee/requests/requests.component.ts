import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatCardModule} from '@angular/material/card';
import {MatTableModule} from '@angular/material/table';
import {VacationRequestService} from '../../../core/services/vacation-request.service';
import {VacationRequest} from '../../../models/vacation-request.model';
import {RouterLink} from '@angular/router';
import {MatButton, MatIconButton} from '@angular/material/button';
import {MatIcon, MatIconModule} from '@angular/material/icon';
import {MatSnackBar} from '@angular/material/snack-bar';

@Component({
  selector: 'app-requests',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatTableModule,
    RouterLink,
    MatButton,
    MatIconModule,
    MatIcon,
    MatIconButton,
  ],
  templateUrl: './requests.component.html',
  styleUrls: ['./requests.component.scss']
})
export class RequestsComponent implements OnInit {
  myRequests: VacationRequest[] = [];
  displayedColumns: string[] = ['from_date', 'to_date', 'total_days', 'reason', 'status', 'actions'];
  loading = false;
  error = '';

  constructor(
    private vacationRequestService: VacationRequestService,
    private snackbar: MatSnackBar
  ) {
  }

  ngOnInit(): void {
    this.loading = true;
    this.vacationRequestService.getAll().subscribe({
      next: (requests) => {
        this.myRequests = requests;
        this.loading = false;
      },
      error: () => {
        this.error = 'Failed to load your requests';
        this.loading = false;
      }
    });
  }

  deleteRequest(id: number): void {
    if (!confirm('Are you sure you want to delete this request?')) return;

    this.vacationRequestService.delete(id).subscribe({
      next: () => {
        this.myRequests = this.myRequests.filter(req => req.id !== id);
        this.snackbar.open('Request deleted successfully', 'Close', {duration: 3000});
      },
      error: (err) => {
        const errorMessage =
          err?.error?.message ||
          err?.error?.error ||
          err?.message ||
          'Failed to delete request';
        this.snackbar.open(errorMessage, 'Close', {duration: 3000});
      }
    });
  }
}

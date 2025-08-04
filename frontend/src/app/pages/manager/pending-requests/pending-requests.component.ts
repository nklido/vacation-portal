import {Component, OnInit} from '@angular/core';
import {VacationRequest} from '../../../models/vacation-request.model';
import {VacationRequestService} from '../../../core/services/vacation-request.service';
import {MatCard, MatCardModule} from '@angular/material/card';
import {
  MatCell, MatCellDef,
  MatColumnDef,
  MatHeaderCell, MatHeaderCellDef,
  MatHeaderRow,
  MatHeaderRowDef,
  MatRow,
  MatRowDef,
  MatTable
} from '@angular/material/table';
import {NgIf} from '@angular/common';
import {MatButtonModule} from '@angular/material/button';
import {MatSnackBar} from '@angular/material/snack-bar';

@Component({
  selector: 'app-pending-requests',
  imports: [
    MatCardModule,
    MatCard,
    MatTable,
    MatHeaderCell,
    MatColumnDef,
    MatCell,
    MatHeaderRow,
    MatRow,
    MatRowDef,
    MatHeaderRowDef,
    MatHeaderCellDef,
    MatCellDef,
    NgIf,
    MatButtonModule
  ],
  templateUrl: './pending-requests.component.html',
  styleUrl: './pending-requests.component.scss'
})
export class PendingRequestsComponent implements OnInit {
  pendingRequests: VacationRequest[] = [];
  loading = false;
  error = '';
  displayedColumns: string[] = ['employee', 'from_date', 'to_date', 'total_days', 'reason', 'status', 'actions'];

  constructor(private vacationRequestService: VacationRequestService, private snackBar: MatSnackBar) {
  }

  ngOnInit(): void {
    this.fetchPendingRequests();
  }

  updateStatus(id: number, status: 'APPROVED' | 'REJECTED') {
    this.vacationRequestService.updateStatus(id, status).subscribe({
      next: () => {
        this.pendingRequests = this.pendingRequests.filter(r => r.id !== id);
      },
      error: (err) => {
        const errorMessage =
          err?.error?.message ||
          err?.error?.error ||
          err?.message ||
          'Something went wrong';

        this.snackBar.open(errorMessage, 'Close', {
          duration: 5000,
          panelClass: ['mat-toolbar', 'mat-warn']
        });
      }
    });
  }

  fetchPendingRequests(): void {
    this.loading = true;
    this.vacationRequestService.getPending().subscribe({
      next: (data) => {
        this.pendingRequests = data;
        this.loading = false;
      },
      error: (err) => {

        const errorMessage =
          err?.error?.message ||
          err?.error?.error ||
          err?.message ||
          'Failed to load pending requests';

        this.snackBar.open(errorMessage, 'Close', {
          duration: 5000,
          panelClass: ['mat-toolbar', 'mat-warn']
        });
        this.loading = false;
      }
    });
  }
}

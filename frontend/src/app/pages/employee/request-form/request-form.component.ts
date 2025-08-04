import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatInputModule} from '@angular/material/input';
import {MatDatepickerModule} from '@angular/material/datepicker';
import {MatNativeDateModule} from '@angular/material/core';
import {MatButtonModule} from '@angular/material/button';
import {MatCardModule} from '@angular/material/card';
import {Router} from '@angular/router';
import {VacationRequestService} from '../../../core/services/vacation-request.service';

@Component({
  selector: 'app-employee-request-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
    MatCardModule
  ],
  templateUrl: './request-form.component.html',
  styleUrls: ['./request-form.component.scss']
})
export class RequestFormComponent implements OnInit {
  form: FormGroup;
  minDate = new Date();

  constructor(
    private fb: FormBuilder,
    private requestService: VacationRequestService,
    private router: Router
  ) {
    this.form = this.fb.group({
      from_date: ['', Validators.required],
      to_date: ['', Validators.required],
      reason: ['', [Validators.required, Validators.minLength(5)]]
    });
  }

  ngOnInit(): void {
  }

  formatDate(date: Date): string {
    return date.toISOString().split('T')[0];
  }

  submit(): void {
    if (this.form.invalid) return;


    const data = {
      from_date: this.formatDate(this.form.value.from_date),
      to_date: this.formatDate(this.form.value.to_date),
      reason: this.form.value.reason
    };

    this.requestService.create(data).subscribe({
      next: () => this.router.navigate(['/employee/requests']),
      error: (err) => {
        const errorMessage =
          err?.error?.message ||
          err?.error?.error ||
          err?.message ||
          'Something went wrong';

        alert(errorMessage);
      }
    });
  }
}

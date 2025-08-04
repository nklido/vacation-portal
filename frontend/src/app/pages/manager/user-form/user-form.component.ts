import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {UserService} from '../../../core/services/user.service';
import {MatCard, MatCardModule, MatCardTitle} from '@angular/material/card';
import {MatFormField, MatInput} from '@angular/material/input';
import {MatButton} from '@angular/material/button';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatSnackBar} from '@angular/material/snack-bar';

@Component({
  selector: 'app-user-form',
  templateUrl: './user-form.component.html',
  imports: [
    MatFormFieldModule,
    MatCardModule,
    MatCardTitle,
    MatCard,
    MatFormField,
    MatFormField,
    MatInput,
    MatFormField,
    ReactiveFormsModule,
    MatButton
  ],
  styleUrls: ['./user-form.component.scss']
})
export class UserFormComponent implements OnInit {
  form!: FormGroup;
  isEdit = false;
  userId?: number;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private snackBar: MatSnackBar
  ) {
  }

  ngOnInit() {
    this.form = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      code: ['', [Validators.required, Validators.pattern(/^\d{7}$/)]],
      password: ['', this.route.snapshot.paramMap.get('id') ? [] : [Validators.required]],
    });

    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEdit = true;
      this.userId = +id;

      this.userService.getById(this.userId).subscribe(user => {
        this.form.patchValue({
          name: user.name,
          email: user.email
        });
        this.form.get('code')?.disable();
        this.form.get('password')?.clearValidators();
        this.form.get('password')?.updateValueAndValidity();
      });
    }
  }

  submit() {
    if (this.form.invalid) return;

    const raw = this.form.getRawValue();

    const userData: any = {
      name: raw.name,
      email: raw.email,
    };

    if (!this.isEdit && raw.code?.trim()) {
      userData.code = raw.code;
    }

    if (!this.isEdit || raw.password?.trim()) {
      userData.password = raw.password;
    }

    const request$ = this.isEdit
      ? this.userService.update(this.userId!, userData)
      : this.userService.create(userData);


    request$.subscribe({
      next: () => this.router.navigate(['/manager/users']),
      error: err => {
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
}

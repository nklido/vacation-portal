import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../auth/auth.service';
import { MatIcon } from '@angular/material/icon';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatToolbar } from '@angular/material/toolbar';
import { MatListModule } from '@angular/material/list';
import { MatButton, MatIconButton } from '@angular/material/button';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { MatLine } from '@angular/material/core';
import {NgForOf} from '@angular/common';
import {User} from '../../../models/user.model';

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [
    MatToolbar,
    MatSidenavModule,
    MatListModule,
    MatIcon,
    RouterOutlet,
    RouterLink,
    RouterLinkActive,
    MatIconButton,
    MatLine,
    NgForOf
  ],
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.scss'
})
export class LayoutComponent implements OnInit {
  opened = true;
  navLinks: { label: string, icon: string, path: string }[] = [];
  title = 'Vacation Portal';
  user: User | null = null;

  constructor(
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit() {
    const data = this.route.snapshot.data;
    this.navLinks = data['navLinks'] || [];
    this.title = data['title'] || this.title;
    this.user = this.authService.getUser();
  }

  toggleSideBar() {
    this.opened = !this.opened;
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
}

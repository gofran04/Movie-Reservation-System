# Movie Reservation System – Requirements

## Problem Statement
The system allows users to browse movies, view showtimes, select seats, and reserve tickets online.

## Functional Requirements

### User
- Browse available movies
- View showtimes for a selected movie
- Select seats for a showtime
- Create a reservation
- View reservation history

### Admin
- Manage movies
- Manage theaters and screens
- Create and manage showtimes
- View reservations

## Non-Functional Requirements
- Prevent double seat booking
- Ensure seat availability consistency
- Enforce database integrity

## Out of Scope
- Real payment processing
- Refunds
- Promotions and discounts

## Core Use Cases

### User Reservation Flow
1. Browse movies
2. Select a movie
3. Choose a showtime
4. View seat availability
5. Select seats
6. Confirm reservation
7. Receive confirmation

### Admin Management Flows

##### 1. Authentication
- Admin login and role-based access

##### 2. Movie Management
- Create, update, list, and delete movies

##### 3. Theater & Screen Management
- Create theaters
- Create and manage screens
- Define seat layouts per screen

##### 4. Showtime Management
- Create, update, cancel showtimes
- Assign movies to screens with date/time

##### 5. Reservation Monitoring
- View and filter reservations

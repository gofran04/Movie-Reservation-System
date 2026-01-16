# Database Schema – Movie Reservation System

This document describes the database schema for the Movie Reservation System.
The system manages a single cinema with multiple halls, movies, showtimes, and seat-based reservations.

---

## cinemas
Represents the single cinema being managed.

Columns:
- id (PK)
- name
- created_at
- updated_at

Notes:
- The system is designed for one cinema only.
- This table may contain a single seeded record.

---

## halls
Represents halls/screens inside the cinema.

Columns:
- id (PK)
- cinema_id (FK → cinemas.id)
- name
- total_rows
- total_columns
- created_at
- updated_at

Constraints:
- cinema_id must reference an existing cinema.

---

## seats
Represents physical seats inside a hall.

Columns:
- id (PK)
- hall_id (FK → halls.id)
- row_number
- column_number
- type (standard, vip)
- created_at
- updated_at

Constraints:
- Each seat belongs to exactly one hall.

---

## movies
Represents movies available for reservation.

Columns:
- id (PK)
- title
- description
- duration_minutes
- rating
- poster
- created_at
- updated_at

---

## showtimes
Represents a scheduled movie in a specific hall at a given time.

Columns:
- id (PK)
- movie_id (FK → movies.id)
- hall_id (FK → halls.id)
- starts_at
- created_at
- updated_at

Constraints:
- movie_id must reference an existing movie.
- hall_id must reference an existing hall.
- UNIQUE (hall_id, starts_at) to prevent double scheduling of the same hall.

Indexes:
- (hall_id, starts_at)

---

## users
Represents system users.

Columns:
- id (PK)
- name
- email (unique)
- phone
- role (admin, client)
- password
- created_at
- updated_at

---

## reservations
Represents a booking made by a user for a showtime.

Columns:
- id (PK)
- user_id (FK → users.id)
- showtime_id (FK → showtimes.id)
- status (pending, confirmed, cancelled)
- created_at
- updated_at

Constraints:
- user_id must reference an existing user.
- showtime_id must reference an existing showtime.

Indexes:
- user_id
- showtime_id

---

## reservation_seats
Pivot table representing reserved seats for a showtime.

Columns:
- id (PK)
- reservation_id (FK → reservations.id)
- seat_id (FK → seats.id)
- showtime_id (FK → showtimes.id)
- created_at

Constraints:
- UNIQUE (showtime_id, seat_id) to prevent double seat booking.
- reservation_id must reference an existing reservation.
- seat_id must reference an existing seat.
- showtime_id must reference an existing showtime.

---

## Data Integrity Strategy

- Seat double booking is prevented at the database level using a unique constraint on (showtime_id, seat_id).
- Admin double scheduling of halls is prevented using a unique constraint on (hall_id, starts_at).
- Reservations are created inside database transactions to ensure consistency under concurrent requests.

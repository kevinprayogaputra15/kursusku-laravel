# Cascade Test (Tinker) — gunakan data dummy saja

Jalankan: `php artisan tinker`

Catatan: status course di proyek ini `draft` / `open` / `closed`
(bukan `active` seperti contoh di PDF), jadi dummy memakai `open`.

## A. Hapus Course dummy

```php
use App\Models\Course;
use App\Models\Participant;
use App\Models\Enrollment;

$course = Course::create([
    'code' => 'TEST-CASCADE-COURSE',
    'name' => 'Course Cascade Test',
    'fee' => 10000,
    'quota' => 5,
    'start_date' => now()->addWeek()->toDateString(),
    'status' => 'open',
]);

$participant = Participant::create([
    'name' => 'Dummy Cascade Course',
    'email' => 'cascade.course@example.test',
]);

Enrollment::create([
    'participant_id' => $participant->id,
    'course_id' => $course->id,
    'status' => 'registered',
]);

Enrollment::where('course_id', $course->id)->count();   // 1
$course->delete();
Enrollment::where('course_id', $course->id)->count();   // 0
Participant::find($participant->id) !== null;           // true
```

## B. Hapus Participant dummy

```php
$course2 = Course::create([
    'code' => 'TEST-CASCADE-PART',
    'name' => 'Participant Cascade Test',
    'fee' => 10000,
    'quota' => 5,
    'start_date' => now()->addDays(10)->toDateString(),
    'status' => 'open',
]);

$participant2 = Participant::create([
    'name' => 'Dummy Participant Delete',
    'email' => 'cascade.participant@example.test',
]);

Enrollment::create([
    'participant_id' => $participant2->id,
    'course_id' => $course2->id,
    'status' => 'registered',
]);

Enrollment::where('participant_id', $participant2->id)->count();  // 1
$participant2->delete();
Enrollment::where('participant_id', $participant2->id)->count();  // 0
Course::find($course2->id) !== null;                              // true
```

## Bersihkan dummy tersisa

```php
$course2->delete();
$participant->delete();
```

Kesimpulan: yang dihapus hanya enrollment terkait. Entity di sisi lain tetap ada.

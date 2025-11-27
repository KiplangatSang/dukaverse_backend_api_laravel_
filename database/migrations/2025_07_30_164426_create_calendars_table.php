 <?php

     use Illuminate\Database\Migrations\Migration;
     use Illuminate\Database\Schema\Blueprint;
     use Illuminate\Support\Facades\Schema;

     return new class extends Migration
     {
         /**
          * Run the migrations.
          */
         public function up(): void
         {
             Schema::create('calendars', function (Blueprint $table) {
                 $table->id();

                 // Foreign key to link the calendar to a user or owner
                 $table->foreignId('user_id')
                     ->nullable()
                     ->constrained('users')
                     ->onDelete('cascade');

                $table->morphs("ownerable");

                 // Event title or calendar entry name
                 $table->string('title');

                 // Detailed description of the event (optional)
                 $table->text('description')->nullable();

                 // Start and end date/time of the event
                 $table->dateTime('start_time');
                 $table->dateTime('end_time')->nullable(); // nullable for all-day events

                 // Mark if this is an all-day event
                 $table->boolean('is_all_day')->default(false);

                 // Status of the event (scheduled, cancelled, completed, etc.)
                 $table->enum('status', ['scheduled', 'cancelled', 'completed'])
                     ->default('scheduled');

                 // Location if the event is physical or virtual (Zoom/Google Meet links)
                 $table->string('location')->nullable();

                 // Color or tag for calendar visualization (optional)
                 $table->string('color_code', 20)->nullable();

                 // Recurrence pattern (optional: daily, weekly, monthly)
                 $table->enum('recurrence', ['none', 'daily', 'weekly', 'monthly'])
                     ->default('none');

                 // Soft delete to allow restoring calendar events
                 $table->softDeletes();


            $table->timestamps();

                 // Index for quick searching
                 $table->index(['user_id', 'start_time']);
             });
         }

         /**
          * Reverse the migrations.
          */
         public function down(): void
         {
             Schema::dropIfExists('calendars');
         }
 };

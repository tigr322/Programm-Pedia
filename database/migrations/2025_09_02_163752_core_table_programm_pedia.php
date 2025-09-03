<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) problems
        Schema::create('problems', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('description')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();
        });

        // 2) solutions  <-- ВАЖНО: до pivot/children
        Schema::create('solutions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('summary')->nullable();
            $t->json('environment')->nullable();
            $t->longText('root_cause')->nullable();
            $t->text('content')->nullable();
            $t->string('pdf_path')->nullable();
            $t->longText('steps');
            $t->longText('verification')->nullable();
            $t->longText('pitfalls')->nullable();
            $t->json('links')->nullable();
            $t->unsignedInteger('score')->default(0);
            $t->timestamps();
        });

        // 3) tech_tags
        Schema::create('tech_tags', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name');
            $t->timestamps();
        });

        // 4) solution_tag (pivot)  <-- обе ссылки уже есть
        Schema::create('solution_tag', function (Blueprint $t) {
            $t->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tech_tag_id')->constrained('tech_tags')->cascadeOnDelete();
            $t->primary(['solution_id','tech_tag_id']);
        });

        // 5) snippets  <-- fk на solutions
        Schema::create('snippets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $t->string('language', 32);
            $t->string('title')->nullable();
            $t->longText('body');
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        // 6) synonyms
        Schema::create('synonyms', function (Blueprint $t) {
            $t->id();
            $t->string('term');
            $t->string('alias');
            $t->timestamps();
            $t->index(['term','alias']);
        });

        // 7) collections
        Schema::create('collections', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('description')->nullable();
            $t->timestamps();
        });

        // 8) collection_solution (pivot)  <-- fk на collections и solutions
        Schema::create('collection_solution', function (Blueprint $t) {
            $t->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $t->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $t->primary(['collection_id','solution_id']);
        });

        // 9) revisions (morphs)
        Schema::create('revisions', function (Blueprint $t) {
            $t->id();
            $t->morphs('revisable'); // revisable_type + revisable_id
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('summary')->nullable();
            $t->json('diff');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('collection_solution');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('synonyms');
        Schema::dropIfExists('snippets');
        Schema::dropIfExists('solution_tag');
        Schema::dropIfExists('tech_tags');
        Schema::dropIfExists('solutions');
        Schema::dropIfExists('problems');
    }
   
};

<x-mail::message>
# Habari {{ $user->name }},

Tunakukumbusha kuendelea na somo lako:

**{{ $lesson->title }}**

Umeshakamilisha:

**{{ $completedTopics }} / {{ $totalTopics }} mada**

Bonyeza hapa kuendelea na somo:

<x-mail::button :url="route('lessons.learn', $lesson->slug)">
Endelea Kujifunza
</x-mail::button>

Asante,  
**Uzima Milele**
</x-mail::message>
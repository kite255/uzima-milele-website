<x-mail::message>
# Habari {{ $user->name }},

Umejiunga kikamilifu na somo:

**{{ $lesson->title }}**

<x-mail::button :url="route('lessons.learn', $lesson->slug)">
Anza Kujifunza
</x-mail::button>

Endelea kujifunza na kukua kiroho kupitia Uzima Milele.

Kwa upendo,  
**Uzima Milele Ministry**
</x-mail::message>
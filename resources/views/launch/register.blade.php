@extends('layouts.app')
@section('title','App Launch Registration')
@section('content')
<section class="max-w-xl mx-auto px-4 py-8">
<div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
<div class="text-center mb-6">
<img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="h-20 object-contain mx-auto mb-4">
<h1 class="text-2xl font-black text-navy">App Launch Registration</h1>
<p class="mt-2 text-gray-600">Karibu katika uzinduzi wa programu ya Uzima Milele.</p>
</div>
@if(session('launch_success'))
<div role="status" class="text-center py-6"><h2 class="text-xl font-bold text-primary">Asante kwa kujisajili!</h2><p class="mt-2 text-gray-600">Tunafurahi kuwa nawe katika uzinduzi huu.</p></div>
@else
<form method="POST" action="{{ route('launch.store') }}" class="space-y-4">
@csrf
@foreach(['name'=>['Jina kamili','text',true],'phone'=>['Namba ya simu','tel',true],'email'=>['Barua pepe','email',false]] as $key=>$field)
<div><label for="{{ $key }}" class="block font-bold text-navy mb-1">{{ $field[0] }}{{ $field[2]?' *':'' }}</label><input id="{{ $key }}" name="{{ $key }}" type="{{ $field[1] }}" value="{{ old($key) }}" @required($field[2]) maxlength="{{ $key==='phone'?25:($key==='name'?150:255) }}" class="w-full rounded-lg border border-gray-300 px-3 py-3">@error($key)<p class="text-red-700 text-sm">{{ $message }}</p>@enderror</div>
@endforeach
<fieldset><legend class="font-bold text-navy mb-2">Jinsia</legend><div class="flex gap-6">@foreach(['male'=>'Me','female'=>'Ke'] as $value=>$label)<label><input type="radio" name="gender" value="{{ $value }}" @checked(old('gender')===$value)> {{ $label }}</label>@endforeach</div>@error('gender')<p class="text-red-700 text-sm">{{ $message }}</p>@enderror</fieldset>
@foreach(['church'=>'Kanisa','college'=>'Chuo'] as $key=>$label)
<div><label for="{{ $key }}" class="block font-bold text-navy mb-1">{{ $label }}</label><input id="{{ $key }}" name="{{ $key }}" value="{{ old($key) }}" maxlength="150" class="w-full rounded-lg border border-gray-300 px-3 py-3">@error($key)<p class="text-red-700 text-sm">{{ $message }}</p>@enderror</div>
@endforeach
<fieldset x-data="{downloaded: @js(old('downloaded',''))}"><legend class="font-bold text-navy mb-2">Je, umepakua programu ya Uzima Milele? *</legend><div class="flex gap-6"><label><input type="radio" name="downloaded" value="1" x-model="downloaded" required @checked(old('downloaded')==='1')> Ndiyo</label><label><input type="radio" name="downloaded" value="0" x-model="downloaded" required @checked(old('downloaded')==='0')> Hapana</label></div><p x-cloak x-show="downloaded==='0'" class="mt-2 text-sm text-gray-600">Utaweza kupakua programu baada ya kukamilisha usajili.</p>@error('downloaded')<p class="text-red-700 text-sm">{{ $message }}</p>@enderror</fieldset>
<p class="text-xs text-gray-600">Taarifa zako zitatumika kwa kumbukumbu za uzinduzi na ujumbe wa kukukaribisha. Kanisa, chuo, jinsia na barua pepe ni za hiari. <a href="{{ route('privacy-policy') }}" class="underline">Sera ya faragha</a>.</p>
<button class="w-full rounded-xl bg-primary text-white font-bold py-3" type="submit">Jisajili</button>
</form>
@endif
<a href="{{ route('app.download.page') }}" class="mt-4 block text-center rounded-xl border border-primary text-primary font-bold py-3">{{ session('launch_success')?'Pakua Programu':'Pakua bila kujisajili' }}</a>
</div></section>
@endsection

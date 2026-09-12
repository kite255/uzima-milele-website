<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailSubscriberController extends Controller
{
    /**
     * Store a new subscriber or reactivate an existing subscriber.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'consent' => [
                    'accepted',
                ],
            ],
            [
                'name.required' =>
                    'Tafadhali weka jina lako.',

                'name.string' =>
                    'Tafadhali weka jina sahihi.',

                'name.max' =>
                    'Jina ni refu sana.',

                'email.required' =>
                    'Tafadhali weka barua pepe.',

                'email.email' =>
                    'Tafadhali weka barua pepe sahihi.',

                'email.max' =>
                    'Barua pepe ni ndefu sana.',

                'phone.string' =>
                    'Tafadhali weka namba ya simu sahihi.',

                'phone.max' =>
                    'Namba ya simu ni ndefu sana.',

                'consent.accepted' =>
                    'Tafadhali kubali kupokea tafakari na taarifa kwa barua pepe.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Clean Submitted Data
        |--------------------------------------------------------------------------
        */

        $fullName = $this->cleanName(
            $validated['name']
        );

        $email = Str::lower(
            trim(
                $validated['email']
            )
        );

        $phone = filled(
            $validated['phone']
            ?? null
        )
            ? trim(
                $validated['phone']
            )
            : null;

        [$firstName, $lastName] =
            $this->splitName(
                $fullName
            );

        /*
        |--------------------------------------------------------------------------
        | Find Existing Subscriber
        |--------------------------------------------------------------------------
        */

        $subscriber =
            EmailSubscriber::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Existing Subscriber
        |--------------------------------------------------------------------------
        */

        if ($subscriber) {
            $subscriber->update([
                'name' =>
                    $fullName,

                'first_name' =>
                    $firstName,

                'last_name' =>
                    $lastName,

                'email' =>
                    $email,

                /*
                 * Do not erase an existing phone number when the
                 * subscription form does not submit one.
                 */
                'phone' =>
                    $phone
                    ?? $subscriber->phone,

                'status' =>
                    'subscribed',

                'subscribed_at' =>
                    now(),

                'unsubscribed_at' =>
                    null,

                'source' =>
                    $subscriber->source
                    ?: 'website',

                'language' =>
                    $subscriber->language
                    ?: 'sw',
            ]);

            $this->attachToDefaultDevotionGroup(
                $subscriber
            );

            return back()->with(
                'subscription_success',
                'Asante! Usajili wako wa Uzima Milele umehakikishwa.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | New Subscriber
        |--------------------------------------------------------------------------
        */

        $subscriber =
            EmailSubscriber::create([
                'name' =>
                    $fullName,

                'first_name' =>
                    $firstName,

                'last_name' =>
                    $lastName,

                'email' =>
                    $email,

                'phone' =>
                    $phone,

                'status' =>
                    'subscribed',

                'subscribed_at' =>
                    now(),

                'unsubscribed_at' =>
                    null,

                'source' =>
                    'website',

                'language' =>
                    'sw',
            ]);

        $this->attachToDefaultDevotionGroup(
            $subscriber
        );

        return back()->with(
            'subscription_success',
            'Asante! Umejiandikisha kupokea tafakari na taarifa kutoka Uzima Milele.'
        );
    }

    /**
     * Unsubscribe a subscriber using their secure token.
     */
    public function unsubscribe(
        string $token
    ): View {
        $subscriber =
            $this->findSubscriberByToken(
                $token
            );

        $alreadyUnsubscribed = (
            $subscriber->status
            === 'unsubscribed'
        );

        if (! $alreadyUnsubscribed) {
            $subscriber->update([
                'status' =>
                    'unsubscribed',

                'unsubscribed_at' =>
                    now(),
            ]);
        }

        return view(
            'subscriptions.unsubscribed',
            [
                'subscriber' =>
                    $subscriber,

                'alreadyUnsubscribed' =>
                    $alreadyUnsubscribed,
            ]
        );
    }

    /**
     * Show subscriber email preferences.
     */
    public function preferences(
        string $token
    ): View {
        $subscriber =
            $this->findSubscriberByToken(
                $token
            );

        return view(
            'subscriptions.preferences',
            compact(
                'subscriber'
            )
        );
    }

    /**
     * Update subscriber information and email preferences.
     */
    public function updatePreferences(
        Request $request,
        string $token
    ): RedirectResponse {
        $subscriber =
            $this->findSubscriberByToken(
                $token
            );

        $validator =
            Validator::make(
                $request->all(),
                [
                    'name' => [
                        'required',
                        'string',
                        'max:200',
                    ],

                    'email' => [
                        'required',
                        'email',
                        'max:255',

                        Rule::unique(
                            'email_subscribers',
                            'email'
                        )->ignore(
                            $subscriber->id
                        ),
                    ],

                    'phone' => [
                        'nullable',
                        'string',
                        'max:30',
                    ],

                    'language' => [
                        'required',

                        Rule::in([
                            'sw',
                            'en',
                        ]),
                    ],

                    'receive_emails' => [
                        'nullable',
                        'boolean',
                    ],
                ],
                [
                    'name.required' =>
                        'Tafadhali weka jina lako.',

                    'name.string' =>
                        'Tafadhali weka jina sahihi.',

                    'name.max' =>
                        'Jina ni refu sana.',

                    'email.required' =>
                        'Tafadhali weka barua pepe.',

                    'email.email' =>
                        'Tafadhali weka barua pepe sahihi.',

                    'email.max' =>
                        'Barua pepe ni ndefu sana.',

                    'email.unique' =>
                        'Barua pepe hii tayari inatumika na msajili mwingine.',

                    'phone.string' =>
                        'Tafadhali weka namba ya simu sahihi.',

                    'phone.max' =>
                        'Namba ya simu ni ndefu sana.',

                    'language.required' =>
                        'Tafadhali chagua lugha.',

                    'language.in' =>
                        'Lugha uliyochagua haitambuliki.',
                ]
            );

        $validated =
            $validator->validate();

        /*
        |--------------------------------------------------------------------------
        | Clean Data
        |--------------------------------------------------------------------------
        */

        $fullName =
            $this->cleanName(
                $validated['name']
            );

        [$firstName, $lastName] =
            $this->splitName(
                $fullName
            );

        $email =
            Str::lower(
                trim(
                    $validated['email']
                )
            );

        $phone =
            filled(
                $validated['phone']
                ?? null
            )
                ? trim(
                    $validated['phone']
                )
                : null;

        $receiveEmails =
            $request->boolean(
                'receive_emails'
            );

        /*
        |--------------------------------------------------------------------------
        | Subscription Status
        |--------------------------------------------------------------------------
        */

        if ($receiveEmails) {
            $status =
                'subscribed';

            $subscribedAt = (
                $subscriber->status
                    === 'subscribed'
                && $subscriber->subscribed_at
            )
                ? $subscriber
                    ->subscribed_at
                : now();

            $unsubscribedAt =
                null;
        } else {
            $status =
                'unsubscribed';

            $subscribedAt =
                $subscriber
                    ->subscribed_at;

            $unsubscribedAt = (
                $subscriber->status
                    === 'unsubscribed'
                && $subscriber
                    ->unsubscribed_at
            )
                ? $subscriber
                    ->unsubscribed_at
                : now();
        }

        /*
        |--------------------------------------------------------------------------
        | Update Subscriber
        |--------------------------------------------------------------------------
        */

        $subscriber->update([
            'name' =>
                $fullName,

            'first_name' =>
                $firstName,

            'last_name' =>
                $lastName,

            'email' =>
                $email,

            'phone' =>
                $phone,

            'language' =>
                $validated[
                    'language'
                ],

            'status' =>
                $status,

            'subscribed_at' =>
                $subscribedAt,

            'unsubscribed_at' =>
                $unsubscribedAt,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Keep Default Devotion Group In Sync When Re-Subscribed
        |--------------------------------------------------------------------------
        */

        if (
            $status
            === 'subscribed'
        ) {
            $this->attachToDefaultDevotionGroup(
                $subscriber
            );
        }

        return redirect()
            ->route(
                'email-subscribers.preferences',
                $subscriber
                    ->unsubscribe_token
            )
            ->with(
                'preferences_success',
                'Mapendeleo yako yamehifadhiwa kwa mafanikio.'
            );
    }

    /**
     * Add a subscriber to the configured default devotion group.
     */
    protected function attachToDefaultDevotionGroup(
        EmailSubscriber $subscriber
    ): void {
        $settings =
            EmailSetting::current();

        if (
            $settings
                ->default_recipient_scope
            !== EmailCampaign::RECIPIENT_SCOPE_GROUP
        ) {
            return;
        }

        if (
            blank(
                $settings
                    ->email_subscriber_group_id
            )
        ) {
            return;
        }

        if (
            ! $settings
                ->subscriberGroup()
                ->exists()
        ) {
            return;
        }

        $subscriber
            ->groups()
            ->syncWithoutDetaching([
                $settings
                    ->email_subscriber_group_id,
            ]);
    }

    /**
     * Find a subscriber using the unsubscribe token.
     */
    protected function findSubscriberByToken(
        string $token
    ): EmailSubscriber {
        return EmailSubscriber::query()
            ->where(
                'unsubscribe_token',
                $token
            )
            ->firstOrFail();
    }

    /**
     * Normalize whitespace in a subscriber's full name.
     */
    protected function cleanName(
        string $name
    ): string {
        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                $name
            )
        );
    }

    /**
     * Split a full name into first name and remaining names.
     */
    protected function splitName(
        string $fullName
    ): array {
        $nameParts =
            preg_split(
                '/\s+/',
                $fullName,
                2
            );

        $firstName =
            $nameParts[0]
            ?? $fullName;

        $lastName =
            isset(
                $nameParts[1]
            )
                ? trim(
                    $nameParts[1]
                )
                : null;

        return [
            $firstName,
            $lastName,
        ];
    }
}
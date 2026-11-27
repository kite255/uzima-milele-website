<?php
namespace Tests\Feature;
use App\Models\LaunchAttendee;
use App\Jobs\SendLaunchWelcome;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
class LaunchRegistrationTest extends TestCase {
    use RefreshDatabase;
    public function test_register_and_prevent_duplicate_welcome(): void {
        Queue::fake();
        $this->get('/app-launch/register')->assertOk()->assertSee('Pakua bila kujisajili');
        $data=['name'=>'Test Guest','phone'=>'0712345678','email'=>'guest@example.com','downloaded'=>'0'];
        $this->post('/app-launch/register',$data)->assertRedirect(route('launch.register'));
        $data['phone']='+255712345678';
        $this->post('/app-launch/register',$data)->assertRedirect(route('launch.register'));
        $this->assertSame(1,LaunchAttendee::count());
        Queue::assertPushed(SendLaunchWelcome::class,2);
    }
    public function test_email_optional(): void {
        Queue::fake();
        $this->post('/app-launch/register',['name'=>'Guest','phone'=>'0751234567','downloaded'=>'1'])->assertRedirect();
        $this->assertDatabaseHas('launch_attendees',['phone'=>'255751234567','email_status'=>'not_provided']);
        Queue::assertPushed(SendLaunchWelcome::class,1);
    }
}

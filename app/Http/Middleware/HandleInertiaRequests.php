namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Default user is null
        $user = null;

        // If the user is authenticated, load their roles
        if ($authenticatedUser = Auth::user()) {
            $authenticatedUser->load('roles'); // Ensure that 'roles' relationship is defined in the User model
            $user = [
                'id' => $authenticatedUser->id,
                'name' => $authenticatedUser->name,
                'email' => $authenticatedUser->email,
                'roles' => $authenticatedUser->roles,
            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user, // Pass the $user object here
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                ];
            },
        ]);
    }
}

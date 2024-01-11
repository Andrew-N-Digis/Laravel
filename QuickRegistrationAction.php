<?php

declare(strict_types=1);

namespace App\Http\Actions\Auth;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Events\VerifyEmail;
use Illuminate\Http\JsonResponse;
use App\Helper\PaymentSystemHelper;
use App\Models\Additional\UserRole;
use Illuminate\Support\Facades\Hash;
use App\Repository\UserRepositoryInterface;
use App\Http\Requests\QuickRegistrationRequest;

final class QuickRegistrationAction
{
    public function __construct(private UserRepositoryInterface $userRepository) {
    }

    public function __invoke(QuickRegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = Carbon::now();
        $user = $this->userRepository->save($data);
        $user->roles()->attach(UserRole::USER);

        event(new VerifyEmail($user->id, $user->email));

        $birthday = (new DateTime($data['birthday']))->format('Y-m-d');

        $this->userRepository->attachProfile($user, [
            'nationality' => $request->get('nationality'),
            'birthday' => $birthday,
            'country_of_residence' => $request->get('country_of_residence')
        ]);

        PaymentSystemHelper::paymentSystemUserUpdate($user, $user->profile, $request);

        return new JsonResponse([
            'accessToken' => $user->createToken('authToken', ['user'])->accessToken
        ]);
    }
}
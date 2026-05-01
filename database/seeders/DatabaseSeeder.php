<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\Role;
use App\Models\Issue;
use App\Models\IssueAuditLog;
use App\Models\User;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laraflow\Contracts\WorkflowRegistryInterface;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();
        $this->seedIssues($users);
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $roster = [
            ['dev1',     'Ada Lovelace',      'ada@symflow.test',      Role::Developer],
            ['dev2',     'Grace Hopper',      'grace@symflow.test',    Role::Developer],
            ['reviewer', 'Linus Torvalds',    'linus@symflow.test',    Role::Reviewer],
            ['qa',       'Margaret Hamilton', 'margaret@symflow.test', Role::Qa],
        ];

        $users = [];

        foreach ($roster as [$key, $name, $email, $role]) {
            $users[$key] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedIssues(array $users): void
    {
        $registry = app(WorkflowRegistryInterface::class);
        $workflow = $registry->get('issue_tracking');

        $samples = [
            [
                'reference' => 'ENG-101',
                'reporter' => 'dev1',
                'assignee' => 'dev1',
                'title' => 'Email verification link expires too quickly',
                'description' => 'Customers report that the verification link expires before they finish setup on mobile.',
                'priority' => Priority::High,
                'label' => 'auth',
                'steps' => [],
            ],
            [
                'reference' => 'ENG-102',
                'reporter' => 'dev2',
                'assignee' => 'dev2',
                'title' => 'Add dark mode to the dashboard',
                'description' => 'OS-level dark mode preference should toggle the dashboard theme.',
                'priority' => Priority::Medium,
                'label' => 'ui',
                'steps' => [
                    ['transition' => 'start_work', 'actor' => 'dev2'],
                ],
            ],
            [
                'reference' => 'ENG-103',
                'reporter' => 'dev1',
                'assignee' => 'dev1',
                'title' => 'Reports queue stalls under high load',
                'description' => 'The reports worker stops processing when more than 200 jobs queue up.',
                'priority' => Priority::Critical,
                'label' => 'infra',
                'steps' => [
                    ['transition' => 'start_work', 'actor' => 'dev1'],
                    ['transition' => 'submit_for_review', 'actor' => 'dev1'],
                ],
            ],
            [
                'reference' => 'ENG-104',
                'reporter' => 'dev2',
                'assignee' => 'dev2',
                'title' => 'Pagination breaks on filtered tables',
                'description' => 'Page 2 of any filtered list returns the unfiltered set.',
                'priority' => Priority::High,
                'label' => 'bug',
                'steps' => [
                    ['transition' => 'start_work', 'actor' => 'dev2'],
                    ['transition' => 'submit_for_review', 'actor' => 'dev2'],
                    ['transition' => 'approve_qa', 'actor' => 'qa'],
                ],
            ],
            [
                'reference' => 'ENG-105',
                'reporter' => 'dev1',
                'assignee' => 'dev1',
                'title' => 'Deprecate /v1/users endpoint',
                'description' => 'Move all callers to /v2/users and remove the legacy controller.',
                'priority' => Priority::Low,
                'label' => 'api',
                'steps' => [
                    ['transition' => 'start_work', 'actor' => 'dev1'],
                    ['transition' => 'submit_for_review', 'actor' => 'dev1'],
                    ['transition' => 'approve_code', 'actor' => 'reviewer'],
                    ['transition' => 'approve_qa', 'actor' => 'qa'],
                    ['transition' => 'merge', 'actor' => 'reviewer', 'reason' => 'Looks great, merging.'],
                ],
            ],
            [
                'reference' => 'ENG-106',
                'reporter' => 'dev2',
                'assignee' => 'dev2',
                'title' => 'Spike: ditch jQuery from settings page',
                'description' => 'Investigate replacing the legacy jQuery code in /settings with vanilla JS.',
                'priority' => Priority::Low,
                'label' => 'tech-debt',
                'steps' => [
                    ['transition' => 'start_work', 'actor' => 'dev2'],
                    ['transition' => 'submit_for_review', 'actor' => 'dev2'],
                    ['transition' => 'reject_code', 'actor' => 'reviewer', 'reason' => 'Closing — let\'s wait until the framework upgrade.'],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $reporter = $users[$sample['reporter']];
            $assignee = $users[$sample['assignee']] ?? null;

            $issue = Issue::query()->create([
                'reference' => $sample['reference'],
                'reporter_id' => $reporter->id,
                'assignee_id' => $assignee?->id,
                'title' => $sample['title'],
                'description' => $sample['description'],
                'priority' => $sample['priority'],
                'label' => $sample['label'],
                'marking' => 'open',
            ]);

            foreach ($sample['steps'] as $step) {
                Auth::login($users[$step['actor']]);
                WorkflowReasonContext::set($step['reason'] ?? null);

                $workflow->apply($issue, $step['transition']);

                if ($step['transition'] === 'start_work') {
                    $issue->started_at = now();
                }
                if (in_array($step['transition'], ['merge', 'close', 'reject_code', 'reject_qa'], true)) {
                    $issue->closed_at = now();
                }
                $issue->save();
            }

            Auth::logout();
        }

        IssueAuditLog::query()->orderBy('id')->get()->each(function (IssueAuditLog $log, int $i) {
            $log->occurred_at = now()->subMinutes((30 - $i) * 23);
            $log->save();
        });
    }
}

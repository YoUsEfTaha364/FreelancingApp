<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications.
     *
     * Retrieve paginated notifications belonging to the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @queryParam page integer The pagination page. Example: 1
     *
     * @response 200 {
     *   "notifications": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": "479e46ba-fa91-4633-a289-948f8c80cc98",
     *         "type": "App\\Notifications\\ProposalSubmitted",
     *         "data": {
     *           "title": "New proposal received",
     *           "message": "Yousef submitted a proposal.",
     *           "action_url": "/jobs/5/proposals/1",
     *           "related_type": "proposal",
     *           "related_id": 1
     *         },
     *         "read_at": null,
     *         "created_at": "2026-11-01T14:52:47.000000Z"
     *       }
     *     ],
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return ResponseService::Response(
            ['notifications' => $notifications],
            'Notifications retrieved successfully',
            200
        );
    }

    /**
     * Get unread notifications.
     *
     * Retrieve only unread notifications belonging to the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @queryParam page integer The pagination page. Example: 1
     *
     * @response 200 {
     *   "notifications": {
     *     "current_page": 1,
     *     "data": [],
     *     "per_page": 15,
     *     "total": 0
     *   }
     * }
     */
    public function unread(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->paginate(15);

        return ResponseService::Response(
            ['notifications' => $notifications],
            'Unread notifications retrieved successfully',
            200
        );
    }

    /**
     * Get unread notifications count.
     *
     * Retrieve the total number of unread notifications.
     *
     * @group Notifications
     * @authenticated
     *
     * @response 200 {
     *   "unread_count": 5
     * }
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        return ResponseService::Response(
            ['unread_count' => $count],
            'Unread notifications count retrieved successfully',
            200
        );
    }

    /**
     * Mark a notification as read.
     *
     * Mark one notification belonging to the authenticated user as read.
     *
     * @group Notifications
     * @authenticated
     *
     * @urlParam id string required The notification UUID. Example: 479e46ba-fa91-4633-a289-948f8c80cc98
     *
     * @response 200 {
     *   "notification": {
     *     "id": "479e46ba-fa91-4633-a289-948f8c80cc98",
     *     "read_at": "2026-11-02T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Not Found"
     * }
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        return ResponseService::Response(
            ['notification' => $notification->fresh()],
            'Notification marked as read',
            200
        );
    }

    /**
     * Mark all notifications as read.
     *
     * Mark all unread notifications belonging to the authenticated user as read.
     *
     * @group Notifications
     * @authenticated
     *
     * @response 200 {
     *   "message": "All notifications marked as read"
     * }
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now()
            ]);

        return ResponseService::Response(
            [],
            'All notifications marked as read',
            200
        );
    }

    /**
     * Delete a notification.
     *
     * Delete a notification belonging to the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @urlParam id string required The notification UUID. Example: 479e46ba-fa91-4633-a289-948f8c80cc98
     *
     * @response 200 {
     *   "message": "Notification deleted successfully"
     * }
     *
     * @response 404 {
     *   "message": "Not Found"
     * }
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->delete();

        return ResponseService::Response(
            [],
            'Notification deleted successfully',
            200
        );
    }
}

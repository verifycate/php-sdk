<?php

declare(strict_types=1);

namespace Verifycate\Sdk;

final class Operations
{
    public const LIST_CERTIFICATES = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/certificates',
        'policy' => 'certificates:read',
        'query' => ['search', 'limit', 'offset'],
        'response' => 'CertificateListOutputBody',
    ];

    public const GET_CERTIFICATE = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/certificates/{id}',
        'policy' => 'certificates:read',
        'response' => 'CertificateDetail',
    ];

    public const CREATE_CERTIFICATE = [
        'method' => 'POST',
        'path' => '/api/v1/workspaces/{slug}/certificates',
        'policy' => 'certificates:write',
        'body' => 'CertificateCreateBody',
        'body_required' => ['name'],
        'response' => 'CertificateDetail',
    ];

    public const UPDATE_CERTIFICATE = [
        'method' => 'PATCH',
        'path' => '/api/v1/workspaces/{slug}/certificates/{id}',
        'policy' => 'certificates:write',
        'body' => 'CertificateUpdateBody',
        'body_required' => [],
        'response' => 'CertificateDetail',
    ];

    public const DELETE_CERTIFICATE = [
        'method' => 'DELETE',
        'path' => '/api/v1/workspaces/{slug}/certificates/{id}',
        'policy' => 'certificates:write',
        'response' => 'DeleteCertificateOutputBody',
    ];

    public const LIST_ISSUES = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/certificates/{id}/issues',
        'policy' => 'certificates:read',
        'query' => [
            'search',
            'status',
            'engagement_status',
            'design_version',
            'limit',
            'offset',
        ],
        'response' => 'ListIssuesOutputBody',
    ];

    public const IMPORT_ISSUES = [
        'method' => 'POST',
        'path' => '/api/v1/workspaces/{slug}/certificates/{id}/issues',
        'policy' => 'certificates:write',
        'body' => 'ImportIssuesBody',
        'body_required' => ['issues'],
        'response' => 'ImportIssuesOutputBody',
    ];

    public const GET_ISSUE = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}',
        'policy' => 'certificates:read',
        'response' => 'CertificateIssue',
    ];

    public const UPDATE_ISSUE = [
        'method' => 'PATCH',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}',
        'policy' => 'certificates:write',
        'body' => 'UpdateIssueBody',
        'body_required' => [],
        'response' => 'CertificateIssue',
    ];

    public const DELETE_ISSUE = [
        'method' => 'DELETE',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}',
        'policy' => 'certificates:write',
        'response' => 'DeleteIssueOutputBody',
    ];

    public const ISSUE_CERTIFICATE = [
        'method' => 'POST',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}/issue',
        'policy' => 'certificates:write',
        'response' => 'CertificateIssue',
    ];

    public const REISSUE_CERTIFICATE = [
        'method' => 'POST',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}/reissue',
        'policy' => 'certificates:write',
        'response' => 'CertificateIssue',
    ];

    public const REMIND_ISSUE = [
        'method' => 'POST',
        'path' => '/api/v1/workspaces/{slug}/issues/{issueID}/remind',
        'policy' => 'certificates:write',
        'response' => 'RemindOutputBody',
    ];

    public const LIST_RECIPIENTS = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/recipients',
        'policy' => 'certificates:read',
        'query' => ['search', 'status', 'certificate', 'limit', 'offset'],
        'response' => 'ListWorkspaceRecipientsOutputBody',
    ];

    public const GET_RECIPIENT = [
        'method' => 'GET',
        'path' => '/api/v1/workspaces/{slug}/recipients/{email}',
        'policy' => 'certificates:read',
        'response' => 'WorkspaceRecipientDetail',
    ];

    public const GET_PUBLIC_CERTIFICATE = [
        'method' => 'GET',
        'path' => '/api/v1/certificates/{publicID}',
        'query' => ['workspace'],
        'public' => true,
        'response' => 'PublicCertificateBody',
    ];

    public const VERIFY_PUBLIC_CERTIFICATE = [
        'method' => 'GET',
        'path' => '/api/v1/certificates/{publicID}/verify',
        'query' => ['workspace'],
        'public' => true,
        'response' => 'VerifyCertificateOutputBody',
    ];

    /**
     * Every operation the SDK calls, keyed by a stable short name.
     *
     * A test asserts each entry still exists in the committed OpenAPI document
     * with the same method, path, API-key policy and parameter names.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'listCertificates' => self::LIST_CERTIFICATES,
            'getCertificate' => self::GET_CERTIFICATE,
            'createCertificate' => self::CREATE_CERTIFICATE,
            'updateCertificate' => self::UPDATE_CERTIFICATE,
            'deleteCertificate' => self::DELETE_CERTIFICATE,
            'listIssues' => self::LIST_ISSUES,
            'importIssues' => self::IMPORT_ISSUES,
            'getIssue' => self::GET_ISSUE,
            'updateIssue' => self::UPDATE_ISSUE,
            'deleteIssue' => self::DELETE_ISSUE,
            'issueCertificate' => self::ISSUE_CERTIFICATE,
            'reissueCertificate' => self::REISSUE_CERTIFICATE,
            'remindIssue' => self::REMIND_ISSUE,
            'listRecipients' => self::LIST_RECIPIENTS,
            'getRecipient' => self::GET_RECIPIENT,
            'getPublicCertificate' => self::GET_PUBLIC_CERTIFICATE,
            'verifyPublicCertificate' => self::VERIFY_PUBLIC_CERTIFICATE,
        ];
    }
}

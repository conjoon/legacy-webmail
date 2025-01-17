<?php
/**
 * conjoon
 * (c) 2007-2015 conjoon.org
 * licensing@conjoon.org
 *
 * conjoon
 * Copyright (C) 2014 Thorsten Suckow-Homberg/conjoon.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
 * $Author$
 * $Id$
 * $Date$
 * $Revision$
 * $LastChangedDate$
 * $LastChangedBy$
 * $URL$
 */


namespace Conjoon\Mail\Server\Protocol;


/**
 * @see \Conjoon\Mail\Server\Protocol\ProtocolAdaptee
 */
require_once 'Conjoon/Mail/Server/Protocol/ProtocolAdaptee.php';


/**
 * A default implementation for a ProtocolAdaptee
 *
 * @package Conjoon
 * @category Conjoon\Mail
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class DefaultProtocolAdaptee implements ProtocolAdaptee {

    /**
     * @var \Conjoon\Data\Repository\Mail\DoctrineMailFolderRepository
     */
    protected $doctrineMailFolderRepository;

    /**
     * @var \Conjoon\Data\Repository\Mail\DoctrineMessageFlagRepository
     */
    protected $doctrineMessageFlagRepository;

    /**
     * @var \Conjoon\Data\Repository\Mail\DoctrineMailAccountRepository
     */
    protected $doctrineMailAccountRepository;

    /**
     * @var \Conjoon\Data\Repository\Mail\DoctrineMessageRepository
     */
    protected $doctrineMessageRepository;

    /**
     * @var \Conjoon\Data\Repository\Mail\DoctrineAttachmentRepository
     */
    protected $attachmentRepository;

    /**
     * @var array
     */
    protected $defaultClassNames = array(
        'folderSecurityService'
            => '\Conjoon\Mail\Client\Security\DefaultFolderSecurityService',
        'folderService'
            => '\Conjoon\Mail\Client\Folder\DefaultFolderService',
        'mailFolderCommons'
            => '\Conjoon\Mail\Client\Folder\DefaultFolderCommons',
        'imapMessageFlagRepository'
            => '\Conjoon\Data\Repository\Mail\ImapMessageFlagRepository',
        'imapMessageRepository'
            => '\Conjoon\Data\Repository\Mail\ImapMessageRepository',
        'imapAttachmentRepository'
            => '\Conjoon\Data\Repository\Mail\ImapAttachmentRepository',
        'accountService'
            => '\Conjoon\Mail\Client\Account\DefaultAccountService'

    );

    /**
     * @var array
     */
    protected $cachedObjects = array(
        'folderSecurityService' => array(),
        'folderService'         => array(),
        'mailFolderCommons'     => array()
    );

    /**
     * Creates a new instance of this protocol adaptee.
     *
     * @param \Conjoon\Data\Repository\Mail\DoctrineMailFolderRepository $doctrineMailFolderRepository
     * @param \Conjoon\Data\Repository\Mail\DoctrineMessageFlagRepository $doctrineMessageFlagRepository
     * @param \Conjoon\Data\Repository\Mail\DoctrineMailAccountRepository $doctrineMailAccountRepository
     * @param \Conjoon\Data\Repository\Mail\DoctrineMessageRepository $doctrineMessageRepository
     * @param \Conjoon\Data\Repository\Mail\DoctrineAttachmentRepository $doctrineAttachmentRepository
     *
     */
    public function __construct(
        \Conjoon\Data\Repository\Mail\DoctrineMailFolderRepository $doctrineMailFolderRepository,
        \Conjoon\Data\Repository\Mail\DoctrineMessageFlagRepository $doctrineMessageFlagRepository,
        \Conjoon\Data\Repository\Mail\DoctrineMailAccountRepository $doctrineMailAccountRepository,
        \Conjoon\Data\Repository\Mail\DoctrineMessageRepository $doctrineMessageRepository,
        \Conjoon\Data\Repository\Mail\DoctrineAttachmentRepository $doctrineAttachmentRepository)
    {
        $this->doctrineMailFolderRepository  = $doctrineMailFolderRepository;
        $this->doctrineMessageFlagRepository = $doctrineMessageFlagRepository;
        $this->doctrineMailAccountRepository = $doctrineMailAccountRepository;
        $this->doctrineMessageRepository     = $doctrineMessageRepository;
        $this->doctrineAttachmentRepository  = $doctrineAttachmentRepository;
    }


    /**
     * @inheritdoc
     */
    public function setFlags(
        \Conjoon\Mail\Client\Message\Flag\FolderFlagCollection $flagCollection,
        \Conjoon\User\User $user)
    {
        $folder = $flagCollection->getFolder();

        try {
            $mayWrite = $this->mayUserWriteFolder($folder, $user);
        } catch (\Conjoon\Mail\Client\Security\SecurityServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
            );
        }

        if (!$mayWrite) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "User must not access the folder \"" . $folder->getNodeId() ."\""
            );
        }

        try {
            $isRemoteMailbox = $this->isFolderRepresentingRemoteMailbox($folder, $user);
        } catch (\Conjoon\Mail\Client\Folder\FolderServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
            );
        }


        if ($isRemoteMailbox) {

            try{
                $account = $this->getAccountServiceForUser($user)
                                ->getMailAccountToAccessRemoteFolder($folder);

                if ($account) {
                    $imapMessageFlagRepository =
                        $this->defaultClassNames['imapMessageFlagRepository'];
                    $imapRepository = new $imapMessageFlagRepository($account);
                    $imapRepository->setFlagsForUser($flagCollection, $user);
                }
            } catch (\Exception $e) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
                );
            }
            if (!$account) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "No mail account found for folder"
                );
            }

        } else {
            try {
                $this->applyFlagCollectionForUser($flagCollection, $user);
            } catch (\Conjoon\Data\Repository\Mail\MailRepositoryException $e) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
                );
            }
        }

        /**
         * @see \Conjoon\Mail\Server\Protocol\DefaultResult\SetFlagsResult
         */
        require_once 'Conjoon/Mail/Server/Protocol/DefaultResult/SetFlagsResult.php';

        return new \Conjoon\Mail\Server\Protocol\DefaultResult\SetFlagsResult();
    }

    /**
     * @inheritdoc
     */
    public function getMessage(
            \Conjoon\Mail\Client\Message\MessageLocation $messageLocation,
            \Conjoon\User\User $user)
    {
        $folder = $messageLocation->getFolder();

        try {
            $mayRead = $this->mayUserReadFolder($folder, $user);
        } catch (\Conjoon\Mail\Client\Security\SecurityServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
            );
        }

        if (!$mayRead) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "User must not access the folder \"" . $folder->getNodeId() ."\""
            );
        }

        try {
            $isRemoteMailbox = $this->isFolderRepresentingRemoteMailbox(
                $folder, $user);
        } catch (\Conjoon\Mail\Client\Folder\FolderServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: " . $e->getMessage(),
                0, $e
            );
        }

        if (!$isRemoteMailbox) {
            $entity = $this->doctrineMessageRepository->findById($messageLocation);
        } else {

            try{
                $account = $this->getAccountServiceForUser($user)
                    ->getMailAccountToAccessRemoteFolder($folder);

                if (!$account) {
                    throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                        "No mail account found for folder"
                    );
                }
                $imapMessageRepository =
                    $this->defaultClassNames['imapMessageRepository'];
                $imapRepository = new $imapMessageRepository($account);

                $entity = $imapRepository->findById($messageLocation);
            } catch (\Exception $e) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "Exception thrown by previous exception: "
                        . $e->getMessage(), 0, $e
                );
            }
        }

        if ($entity == null) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Message not found"
            );
        }


        /**
         * @see \Conjoon\Mail\Server\Protocol\DefaultResult\GetMessageResult
         */
        require_once 'Conjoon/Mail/Server/Protocol/DefaultResult/GetMessageResult.php';

        return new \Conjoon\Mail\Server\Protocol\DefaultResult\GetMessageResult(
            $entity,$messageLocation
        );

    }

    /**
     * @inheritdoc
     */
    public function getAttachment(
        \Conjoon\Mail\Client\Message\AttachmentLocation $attachmentLocation,
        \Conjoon\User\User $user)
    {

        $messageLocation = $attachmentLocation->getMessageLocation();

        $folder = $messageLocation->getFolder();

        try {
            $mayRead = $this->mayUserReadFolder($folder, $user);
        } catch (\Conjoon\Mail\Client\Security\SecurityServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: "
                    . $e->getMessage(), 0, $e
            );
        }

        if (!$mayRead) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "User must not access the folder \"" . $folder->getNodeId() ."\""
            );
        }

        try {
            $isRemoteMailbox = $this->isFolderRepresentingRemoteMailbox(
                $folder, $user);
        } catch (\Conjoon\Mail\Client\Folder\FolderServiceException $e) {
            throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                "Exception thrown by previous exception: " . $e->getMessage(),
                0, $e
            );
        }

        if (!$isRemoteMailbox) {

            $entity = $this->doctrineAttachmentRepository->findById($attachmentLocation);

        } else {

            try{
                $account = $this->getAccountServiceForUser($user)
                    ->getMailAccountToAccessRemoteFolder($folder);

                if ($account) {
                    $imapAttachmentRepository =
                        $this->defaultClassNames['imapAttachmentRepository'];
                    $imapRepository = new $imapAttachmentRepository($account);

                    $entity = $imapRepository->findById($attachmentLocation);

                    if ($entity == null) {
                        throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                            "Message not found"
                        );
                    }

                }
            } catch (\Exception $e) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "Exception thrown by previous exception: "
                        . $e->getMessage(), 0, $e
                );
            }
            if (!$account) {
                throw new \Conjoon\Mail\Server\Protocol\ProtocolException(
                    "No mail account found for folder"
                );
            }

        }

        /**
         * @see \Conjoon\Mail\Server\Protocol\DefaultResult\GetMessageResult
         */
        require_once 'Conjoon/Mail/Server/Protocol/DefaultResult/GetAttachmentResult.php';

        return new \Conjoon\Mail\Server\Protocol\DefaultResult\GetAttachmentResult(
            $entity, $attachmentLocation
        );

    }



// -------- helper API

    /**
     * Returns the DoctrineMailAccountRepository this class was configured with.
     *
     * @return \Conjoon\Data\Mail\DoctrineMailAccountRepository
     */
    protected function getDoctrineMailAccountRepository()
    {
        return $this->doctrineMailAccountRepository;
    }

    /**
     * Returns the DoctrineMailFolderRepository this class was configured with.
     *
     * @return \Conjoon\Data\Mail\DoctrineMailFolderRepository
     */
    protected function getDoctrineMailFolderRepository()
    {
        return $this->doctrineMailFolderRepository;
    }

    /**
     * Returns the DoctrineMessageFlagRepository this class was configured with.
     *
     * @return \Conjoon\Data\Mail\DoctrineMessageFlagRepository
     */
    protected function getDoctrineMessageFlagRepository()
    {
        return $this->doctrineMessageFlagRepository;
    }

    /**
     * Returns the accountservice for the specified user.
     *
     * @param \Conjoon\User\User $user
     *
     * @return \Conjoon\Mail\Client\Account\AccountService
     */
    protected function getAccountServiceForUser(\Conjoon\User\User $user)
    {
        return $this->getServiceForUser('accountService', $user);
    }

    /**
     * Returns the folder security service for the specified user.
     *
     * @param \Conjoon\User\User $user
     *
     * @return \Conjoon\Mail\Client\Security\FolderSecurityService
     */
    protected function getFolderSecurityServiceForUser(\Conjoon\User\User $user)
    {
        return $this->getServiceForUser('folderSecurityService', $user);
    }

    /**
     * Returns the service for the specified user.
     *
     * @param \Conjoon\User\User $user
     *
     * @return mixed
     */
    protected function getServiceForUser($serviceName, \Conjoon\User\User $user)
    {
        $id = spl_object_hash($user);

        if (empty($this->cachedObjects[$serviceName])) {

            $className = $this->defaultClassNames[$serviceName];

            $instance = null;

            switch ($serviceName) {

                case 'mailFolderCommons':

                    $instance = new $className(array(
                        'user'                 => $user,
                        'mailFolderRepository' => $this->getDoctrineMailFolderRepository()
                    ));

                    break;

                case 'folderSecurityService':

                    $instance = new $className(array(
                        'mailFolderRepository' => $this->getDoctrineMailFolderRepository(),
                        'user'                 => $user,
                        'mailFolderCommons'    => $this->getServiceForUser(
                                                      'mailFolderCommons', $user
                                                  )
                    ));
                    break;

                case 'folderService':

                    $instance = new $className(array(
                        'mailFolderRepository' => $this->getDoctrineMailFolderRepository(),
                        'user'                 => $user,
                        'mailFolderCommons'    => $this->getServiceForUser(
                                                      'mailFolderCommons', $user
                                                  )
                    ));
                    break;

                case 'accountService':
                    $instance = new $className(array(
                        'user'                 => $user,
                        'folderService'        => $this->getServiceForUser(
                                                      'folderService', $user
                                                  ),
                        'mailAccountRepository' => $this->getDoctrineMailAccountRepository()
                    ));
                    break;
            }

            $this->cachedObjects[$serviceName][$id] = $instance;
        }

        return $this->cachedObjects[$serviceName][$id];
    }

    /**
     * Returns true if the folder represents a remote mailbox, otherwise false.
     *
     * @param \Conjoon\Mail\Client\Folder\Folder $folder
     *
     * @return boolean
     *
     * @throws \Conjoon\Mail\Client\Folder\FolderServiceException
     */
    protected function isFolderRepresentingRemoteMailbox(
        \Conjoon\Mail\Client\Folder\Folder $folder, \Conjoon\User\User $user)
    {
        return $this->getServiceForUser('folderService', $user)
                    ->isFolderRepresentingRemoteMailbox($folder);

    }

    /**
     *
     * @param \Conjoon\Mail\Client\Folder\Folder $folder
     * @param \Conjoon\User\User $user
     *
     *
     * @throws \Conjoon\Mail\Client\Security\SecurityServiceException
     */
    protected function mayUserWriteFolder(
        \Conjoon\Mail\Client\Folder\Folder $folder, \Conjoon\User\User $user)
    {
        $folderSecurityService = $this->getFolderSecurityServiceForUser($user);

        return $folderSecurityService->isFolderAccessible($folder);
    }

    /**
     *
     * @param \Conjoon\Mail\Client\Folder\Folder $folder
     * @param \Conjoon\User\User $user
     *
     *
     * @throws \Conjoon\Mail\Client\Security\SecurityServiceException
     */
    protected function mayUserReadFolder(
        \Conjoon\Mail\Client\Folder\Folder $folder, \Conjoon\User\User $user)
    {
        $folderSecurityService = $this->getFolderSecurityServiceForUser($user);

        return $folderSecurityService->isFolderAccessible($folder);
    }

    /**
     * Applies the message flags for the specified user.
     *
     * @param \Conjoon\Mail\Client\Message\Flag\FolderFlagCollection $flagCollection
     * @param \Conjoon\User\User $user
     *
     * @throws \Conjoon\Data\Repository\Mail\MailRepositoryException
     */
    protected function applyFlagCollectionForUser(
        \Conjoon\Mail\Client\Message\Flag\FolderFlagCollection $folderFlagCollection,
        \Conjoon\User\User $user)
    {
        $this->getDoctrineMessageFlagRepository()->setFlagsForUser(
            $folderFlagCollection, $user
        );
    }

}

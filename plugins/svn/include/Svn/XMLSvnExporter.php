<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Svn;

use Logger;
use Tuleap\Project\XML\Export\ArchiveInterface;
use Tuleap\Svn\Repository\RepositoryManager;
use Tuleap\Svn\Repository\Repository;
use Tuleap\Svn\AccessControl\AccessFileReader;
use Tuleap\Svn\Admin\MailNotificationManager;
use Project;
use SimpleXMLElement;
use XML_SimpleXMLCDATAFactory;
use System_Command;
use ForgeConfig;

class XMLSvnExporter
{
    /**
     * RepositoryManager
     */
    private $repository_manager;

    /**
     * Project
     */
    private $project;

    /**
     * SvnDump
     */
    private $svn_admin;

    /**
     * XML_SimpleXMLCDATAFactory
     */
    private $cdata_section_factory;

    /**
     * MailNotificationManager
     */
    private $mail_notification_manager;

    /**
     * System_Command
     */
    private $system_command;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        RepositoryManager $repository_manager,
        Project $project,
        SvnAdmin $svn_admin,
        XML_SimpleXMLCDATAFactory $cdata_section_factory,
        MailNotificationManager $mail_notification_manager,
        System_Command $system_command,
        Logger $logger
    ) {
        $this->repository_manager        = $repository_manager;
        $this->project                   = $project;
        $this->svn_admin                 = $svn_admin;
        $this->cdata_section_factory     = $cdata_section_factory;
        $this->mail_notification_manager = $mail_notification_manager;
        $this->system_command            = $system_command;
        $this->logger                    = $logger;
    }

    public function exportToXml(SimpleXMLElement $xml_content, ArchiveInterface $archive, $temporary_dump_path_on_filesystem)
    {
        $root_node = $xml_content->addChild("svn");

        $repositories = $this->repository_manager->getRepositoriesInProject($this->project);
        foreach ($repositories as $repository) {
            $this->logger->info('dumping ' . $repository->getName());
            $node_repository = $this->dumpRepository($root_node, $repository, $temporary_dump_path_on_filesystem);

            $export_dump_file_name = $repository->getName().".svn";
            if ($archive->isADirectory() === true) {
                $archive->addEmptyDir('svn');
            }
            $archive->addFile('svn/' . $export_dump_file_name, $temporary_dump_path_on_filesystem . '/' . $export_dump_file_name);

            $this->dumpAccessFile($node_repository, $repository);
            $this->dumpNotifications($node_repository, $repository);
        }
    }

    private function dumpNotifications(SimpleXMLElement $node, Repository $repository)
    {
        foreach ($this->mail_notification_manager->getByRepository($repository) as $notification) {
            $node_notification = $node->addChild("notification");
            $node_notification->addAttribute("path", $notification->getPath());
            $node_notification->addAttribute("emails", $notification->getNotifiedMailsAsString());
        }
    }

    private function dumpAccessFile(SimpleXMLElement $node, Repository $repository)
    {
        $accessfile_reader  = new AccessFileReader($repository);
        $custom_access_file = $accessfile_reader->readContentBlock($repository);

        $this->cdata_section_factory->insert($node, "access-file", $custom_access_file);
    }

    private function dumpRepository(SimpleXMLElement $node, Repository $repository, $temporary_dump_path_on_filesystem)
    {
        $node_repository = $node->addChild("repository");
        $node_repository->addAttribute("name", $repository->getName());

        $this->svn_admin->dumpRepository($repository, $temporary_dump_path_on_filesystem);
        $node_repository->addAttribute(
            "dump-file",
            "svn/" . $this->getExportDumpFile($repository)
        );

        return $node_repository;
    }

    private function getExportDumpFile(Repository $repository)
    {
        return $repository->getName() . ".svn";
    }
}

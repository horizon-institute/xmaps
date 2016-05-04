<?php
/**
 * Database manipulation functionality.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Database manipulation functionality.
 */
class XMapsDatabase {

	const LOCATION_TABLE_SUFFIX = 'map_object_locations';

	/**
	 * Creates custom tables for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	public static function create_tables( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id ) 
		. self::LOCATION_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		reference_id BIGINT(20) NOT NULL,
		reference_type VARCHAR(20) NOT NULL,
		location GEOMETRY
		);";
		dbDelta( $sql, true );
	}
	
	public static function get_map_object_locations( $reference_id ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id ) 
		. self::LOCATION_TABLE_SUFFIX;
		$sql = $wpdb->prepare( 
		"SELECT id, reference_id, reference_type, ST_AsBinary(location) AS location
		FROM $tbl_name WHERE reference_id = %d", $reference_id );
		return $wpdb->get_results( $sql, OBJECT );
	}
}
/**SET ANSI_NULLS ON
 GO
 SET QUOTED_IDENTIFIER ON
 GO
 CREATE PROCEDURE [dbo].[SelectObjectsWithinBounds]
 @north float,
 @south float,
 @east float,
 @west float,
 @contextID bigint
 AS
 BEGIN
 SET NOCOUNT ON;
 DECLARE @area geography;
 DECLARE @areapoly nvarchar(MAX);
 SET @areapoly = 'POLYGON((:west :south, :east :south, :east :north, :west :north, :west :south))'
 SET @areapoly = REPLACE(@areapoly, ':north', CONVERT(nvarchar(MAX), @north))
 SET @areapoly = REPLACE(@areapoly, ':south', CONVERT(nvarchar(MAX), @south))
 SET @areapoly = REPLACE(@areapoly, ':east', CONVERT(nvarchar(MAX), @east))
 SET @areapoly = REPLACE(@areapoly, ':west', CONVERT(nvarchar(MAX), @west))

 CREATE TABLE #objectsr (
 ID BIGINT NOT NULL,
 ContextID BIGINT NOT NULL,
 URI NVARCHAR(MAX) NOT NULL,
 Locations NVARCHAR(MAX),
 Actions NVARCHAR(MAX));
 DECLARE @objectid BIGINT;

 BEGIN TRY
 SET @area = geography::STGeomFromText(@areapoly, 4326);
 DECLARE objectsc CURSOR FOR
 SELECT
 DISTINCT l.ObjectID AS ObjectID
 FROM
 Location l, LocationPoint lp
 WHERE
 l.ID = lp.LocationID
 AND l.ContextID = @ContextID
 AND @area.STIntersects(lp.Center) = 1;
 END TRY
 BEGIN CATCH
 DECLARE objectsc CURSOR FOR
 SELECT
 DISTINCT l.ObjectID AS ObjectID
 FROM
 Location l, LocationPoint lp
 WHERE
 l.ID = lp.LocationID
 AND l.ContextID = @ContextID;
 END CATCH

 OPEN objectsc;
 FETCH NEXT FROM objectsc INTO @objectid;
 WHILE @@FETCH_STATUS = 0
 BEGIN
 INSERT INTO #objectsr(ID, ContextID, URI, Locations, Actions)
 SELECT o.ID, o.ContextID, o.URI,
 (SELECT
 l.ID AS ID,
 l.Source AS Source,
 lp.CenterText AS CenterText,
 lp.Error AS Error
 FROM
 Location l,
 LocationPoint lp
 WHERE
 l.ObjectID = o.ID
 AND l.ID = lp.LocationID
 FOR XML PATH ('Location'), ROOT ('Locations')) AS Locations,
 (SELECT
 a.ID AS ID,
 a.URI AS URI,
 a.UserID AS UserID,
 a.[DateTime] AS [DateTime]
 FROM [Action] a
 WHERE a.ObjectID = o.ID
 FOR XML PATH ('Action'), ROOT ('Actions')) AS Actions
 FROM ObjectOfInterest o
 WHERE o.ID = @objectid;
 FETCH NEXT FROM objectsc INTO @objectid;
 END;
 CLOSE objectsc;
 DEALLOCATE objectsc;
 SELECT * FROM #objectsr;
 DROP TABLE #objectsr;
 END*/
?>
